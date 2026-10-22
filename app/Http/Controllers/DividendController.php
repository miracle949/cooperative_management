<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Dividend;
use App\Models\DividendSetting;
use App\Models\PatronageRefundDistribution;
use App\Models\savings_account_tbl;
use App\Models\savings_transaction_tbl;
use App\Models\share_capital_account_tbl;
use App\Models\Users_tbl;
use App\Services\PatronageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DividendController extends Controller
{
    private function getDividendData(Request $request)
    {
        $year = $request->get('year', now()->year);

        $distribution = DB::table('dividend_distributions')
            ->where('year', $year)
            ->first();

        $dividends = collect();
        $approvedCount = 0;
        $disbursedCount = 0;
        $pendingCount = 0;
        $totalSumShareCapital = 0;
        $totalSumRecommended = 0;
        $totalSumApproved = 0;

        // Patronage data
        $patronageDistributions = collect();
        $patronageApprovedCount = 0;
        $patronageDisbursedCount = 0;
        $patronagePendingCount = 0;
        $totalSumPatronage = 0;
        $totalSumPatronageApproved = 0;

        if ($distribution) {
            $dividends = Dividend::with('user')
                ->where('year', $year)
                ->orderBy('id')
                ->paginate(10)
                ->appends(['year' => $year]);

            $approvedCount = Dividend::where('year', $year)->where('status', 'approved')->count();
            $disbursedCount = Dividend::where('year', $year)->where('status', 'disbursed')->count();
            $pendingCount = Dividend::where('year', $year)->where('status', 'pending')->count();
            $totalSumShareCapital = Dividend::where('year', $year)->sum('share_capital_amount');
            $totalSumRecommended = Dividend::where('year', $year)->sum('recommended_amount');
            $totalSumApproved = Dividend::where('year', $year)->sum('approved_amount');

            // Patronage
            $patronageDistributions = PatronageRefundDistribution::with('user')
                ->where('year', $year)
                ->orderBy('id')
                ->paginate(10, ['*'], 'patronage_page')
                ->appends(['year' => $year]);

            $patronageApprovedCount = PatronageRefundDistribution::where('year', $year)->where('status', 'approved')->count();
            $patronageDisbursedCount = PatronageRefundDistribution::where('year', $year)->where('status', 'disbursed')->count();
            $patronagePendingCount = PatronageRefundDistribution::where('year', $year)->where('status', 'pending')->count();
            $totalSumPatronage = PatronageRefundDistribution::where('year', $year)->sum('total_patronage');
            $totalSumPatronageApproved = PatronageRefundDistribution::where('year', $year)->where('status', 'approved')->sum('amount');
        }

        $years = DB::table('dividend_distributions')
            ->orderByDesc('year')
            ->pluck('year');

        $currentYear = now()->year;

        $dividendSetting = DividendSetting::where('year', $year)->first();
        $dividendFundPercentage = $dividendSetting ? $dividendSetting->dividend_fund_percentage : 60.00;
        $patronageFundPercentage = $dividendSetting ? $dividendSetting->patronage_fund_percentage : 40.00;
        $patronageBasis = ($distribution && ! empty($distribution->patronage_basis))
            ? $distribution->patronage_basis
            : ($dividendSetting ? $dividendSetting->patronage_basis : 'total_repayment');
        $reserveFundPercentage = $dividendSetting ? $dividendSetting->reserve_fund_percentage : 10.00;
        $cetfPercentage = $dividendSetting ? $dividendSetting->cetf_percentage : 10.00;
        $cdfPercentage = $dividendSetting ? $dividendSetting->cdf_percentage : 3.00;
        $optionalFundPercentage = $dividendSetting ? $dividendSetting->optional_fund_percentage : 7.00;
        $statutoryTotalPercentage = $reserveFundPercentage + $cetfPercentage + $cdfPercentage + $optionalFundPercentage;
        $remainingSurplusPercentage = 100 - $statutoryTotalPercentage;

        return compact(
            'distribution',
            'dividends',
            'year',
            'years',
            'currentYear',
            'approvedCount',
            'disbursedCount',
            'pendingCount',
            'totalSumShareCapital',
            'totalSumRecommended',
            'totalSumApproved',
            'dividendFundPercentage',
            'patronageDistributions',
            'patronageApprovedCount',
            'patronageDisbursedCount',
            'patronagePendingCount',
            'totalSumPatronage',
            'totalSumPatronageApproved',
            'patronageFundPercentage',
            'patronageBasis',
            'reserveFundPercentage',
            'cetfPercentage',
            'cdfPercentage',
            'optionalFundPercentage',
            'statutoryTotalPercentage',
            'remainingSurplusPercentage'
        );
    }

    private function ensureGeneralManager()
    {
        if (! \App\Services\SoDGuard::isGeneralManager()) {
            abort(403, 'Only the General Manager can access disbursal management.');
        }
    }

    public function tablePartial(Request $request)
    {
        $this->ensureGeneralManager();

        $data = $this->getDividendData($request);

        return view('admin_components.dividends_table_partial', $data);
    }

    public function patronageTablePartial(Request $request)
    {
        $data = $this->getDividendData($request);

        return view('admin_components.patronage_table_partial', $data);
    }

    public function index(Request $request)
    {
        $this->ensureGeneralManager();

        $data = $this->getDividendData($request);

        if ($request->ajax()) {
            return view('admin_components.dividends_table_partial', $data);
        }

        return view('admin_components.dividends', $data);
    }

    public function calculate(Request $request)
    {
        $this->ensureGeneralManager();

        $validator = Validator::make($request->all(), [
            'net_surplus' => 'required|numeric|min:1',
            'year' => 'required|integer|min:2000|max:2100',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $netSurplus = $request->net_surplus;
        $year = $request->year;

        $existingDistribution = DB::table('dividend_distributions')
            ->where('year', $year)
            ->first();

        $reusePatronageBasis = null;
        if ($existingDistribution) {
            // Check if regeneration is allowed (only pending records)
            $hasApproved = Dividend::where('year', $year)->where('status', 'approved')->exists()
                || PatronageRefundDistribution::where('year', $year)->where('status', 'approved')->exists();

            $hasDisbursed = Dividend::where('year', $year)->where('status', 'disbursed')->exists()
                || PatronageRefundDistribution::where('year', $year)->where('status', 'disbursed')->exists();

            if ($hasApproved || $hasDisbursed) {
                return redirect()->route('dividends.index', ['year' => $year])
                    ->with('error', 'Cannot regenerate. Approved or disbursed records exist for year '.$year.'. Reset the annual distribution first.');
            }

            // Preserve the previously stored patronage basis so a regeneration
            // keeps the year's snapshot authoritative (strict freeze).
            if (! empty($existingDistribution->patronage_basis)) {
                $reusePatronageBasis = $existingDistribution->patronage_basis;
            }

            // Only pending records exist — delete and regenerate
            Dividend::where('year', $year)->where('status', 'pending')->delete();
            PatronageRefundDistribution::where('year', $year)->where('status', 'pending')->delete();

            DB::table('dividend_distributions')->where('year', $year)->delete();
        }

        $dividendSetting = DividendSetting::getForYear($year);

        // Freeze: on regeneration reuse the stored basis; only a brand-new
        // distribution (or one that went through resetDistribution) captures
        // the current setting.
        $patronageBasisForInsert = $reusePatronageBasis
            ?: ($dividendSetting->patronage_basis ?: 'total_repayment');

        $reserveFundPct = (float) $dividendSetting->reserve_fund_percentage;
        $cetfPct = (float) $dividendSetting->cetf_percentage;
        $cdfPct = (float) $dividendSetting->cdf_percentage;
        $optionalPct = (float) $dividendSetting->optional_fund_percentage;
        $statutoryTotalPct = $reserveFundPct + $cetfPct + $cdfPct + $optionalPct;

        if ($statutoryTotalPct > 100) {
            return redirect()->route('dividends.index', ['year' => $year])
                ->with('error', 'Statutory fund allocations cannot exceed 100% of net surplus. Adjust the statutory percentages in Finance settings first.');
        }

        $reserveFund = round($netSurplus * $reserveFundPct / 100, 2);
        $educationFund = round($netSurplus * $cetfPct / 100, 2);
        $communityFund = round($netSurplus * $cdfPct / 100, 2);
        $optionalFund = round($netSurplus * $optionalPct / 100, 2);
        $totalStatutory = $reserveFund + $educationFund + $communityFund + $optionalFund;
        $remainingSurplus = round($netSurplus - $totalStatutory, 2);

        $dividendFundPct = $dividendSetting->dividend_fund_percentage / 100;
        $patronageRefundPct = $dividendSetting->patronage_fund_percentage / 100;

        $dividendPool = round($remainingSurplus * $dividendFundPct, 2);
        $patronageRefundPool = round($remainingSurplus * $patronageRefundPct, 2);

        $members = Users_tbl::where('role', 'member')->get();
        $totalShareCapital = 0;
        $memberCapitals = [];

        foreach ($members as $member) {
            $account = share_capital_account_tbl::where('user_id', $member->id)->first();
            $capitalAmount = $account ? $account->total_amount : 0;
            $memberCapitals[$member->id] = $capitalAmount;
            $totalShareCapital += $capitalAmount;
        }

        DB::beginTransaction();

        try {
            DB::table('dividend_distributions')->insert([
                'year' => $year,
                'net_surplus' => $netSurplus,
                'reserve_fund' => $reserveFund,
                'education_fund' => $educationFund,
                'community_fund' => $communityFund,
                'optional_fund' => $optionalFund,
                'reserve_fund_percentage' => $reserveFundPct,
                'cetf_percentage' => $cetfPct,
                'cdf_percentage' => $cdfPct,
                'optional_fund_percentage' => $optionalPct,
                'statutory_total_percentage' => $statutoryTotalPct,
                'remaining_surplus' => $remainingSurplus,
                'dividend_pool' => $dividendPool,
                'patronage_refund_pool' => $patronageRefundPool,
                'patronage_basis' => $patronageBasisForInsert,
                'status' => 'draft',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($members as $member) {
                $capitalAmount = $memberCapitals[$member->id];

                $recommended = 0;
                if ($totalShareCapital > 0) {
                    $recommended = round(
                        ($capitalAmount / $totalShareCapital) * $dividendPool,
                        2
                    );
                }

                Dividend::create([
                    'user_id' => $member->id,
                    'year' => $year,
                    'share_capital_amount' => $capitalAmount,
                    'recommended_amount' => $recommended,
                    'approved_amount' => $recommended,
                    'status' => 'pending',
                ]);
            }

            DB::commit();

            AuditLog::log(
                'Calculated Dividends',
                "Generated dividend calculations for year {$year} with net surplus of ₱".number_format($netSurplus, 2),
                'dividend',
                null
            );

            return redirect()->route('dividends.index', ['year' => $year])
                ->with('success', "Distribution for {$year} generated successfully. Dividend pool: ₱".number_format($dividendPool, 2).'. Generate patronage refund allocations separately.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to generate calculations: '.$e->getMessage());
        }
    }

    public function calculatePatronageRefunds(Request $request)
    {
        $year = $request->get('year', now()->year);

        $validator = Validator::make(['year' => $year], [
            'year' => 'required|integer|min:2000|max:2100',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $distribution = DB::table('dividend_distributions')
            ->where('year', $year)
            ->first();

        if (! $distribution) {
            return redirect()->route('dividends.index', ['year' => $year])
                ->with('error', 'Generate the annual distribution first before calculating patronage refunds.');
        }

        // Check if approved or disbursed patronage refund records already exist
        $hasApproved = PatronageRefundDistribution::where('year', $year)->where('status', 'approved')->exists();
        $hasDisbursed = PatronageRefundDistribution::where('year', $year)->where('status', 'disbursed')->exists();

        if ($hasApproved || $hasDisbursed) {
            return redirect()->route('dividends.index', ['year' => $year])
                ->with('error', 'Cannot generate patronage refunds. Approved or disbursed patronage refund records exist for year '.$year.'. Reset the distribution first.');
        }

        try {
            // Delete any existing pending patronage refund records and regenerate
            PatronageRefundDistribution::where('year', $year)->where('status', 'pending')->delete();

            $patronageService = new PatronageService;
            $patronageService->generatePatronageRefundDistributions($year);

            AuditLog::log(
                'Generated Patronage Refund Calculations',
                "Generated patronage refund allocations for year {$year}. Pool: ₱".number_format($distribution->patronage_refund_pool, 2),
                'patronage_refund',
                null
            );

            return redirect()->route('dividends.index', ['year' => $year])
                ->with('success', 'Patronage refund allocations generated for '.$year.'.');
        } catch (\RuntimeException $e) {
            return redirect()->route('dividends.index', ['year' => $year])
                ->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('error', 'Failed to generate patronage refunds: '.$e->getMessage());
        }
    }

    public function resetDistribution(Request $request, $year)
    {
        $this->ensureGeneralManager();

        $hasDisbursed = Dividend::where('year', $year)->where('status', 'disbursed')->exists()
            || PatronageRefundDistribution::where('year', $year)->where('status', 'disbursed')->exists();

        if ($hasDisbursed) {
            return redirect()->back()
                ->with('error', 'Cannot reset distribution for '.$year.'. Disbursed records exist — money has already been released.');
        }

        DB::beginTransaction();

        try {
            Dividend::where('year', $year)->where('status', 'approved')->update(['status' => 'pending']);
            PatronageRefundDistribution::where('year', $year)->where('status', 'approved')->update(['status' => 'pending']);

            DB::table('dividend_distributions')->where('year', $year)->update([
                'status' => 'draft',
                'patronage_basis' => null,
                'updated_at' => now(),
            ]);

            DB::commit();

            AuditLog::log(
                'Reset Annual Distribution',
                "Reset annual distribution for year {$year}. Approved records reverted to pending.",
                'dividend',
                null
            );

            return redirect()->route('dividends.index', ['year' => $year])
                ->with('success', 'Distribution for '.$year.' has been reset. You can now regenerate.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to reset distribution: '.$e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $this->ensureGeneralManager();

        $validator = Validator::make($request->all(), [
            'approved_amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Invalid amount'], 422);
        }

        $dividend = Dividend::findOrFail($id);

        if ($dividend->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Only pending dividends can be edited.'], 400);
        }

        $oldAmount = $dividend->approved_amount;
        $dividend->approved_amount = $request->approved_amount;
        $dividend->save();

        AuditLog::log(
            'Updated Dividend',
            "Updated dividend for {$dividend->user->first_name} {$dividend->user->last_name} (Year: {$dividend->year}) from ₱".number_format($oldAmount, 2).' to ₱'.number_format($dividend->approved_amount, 2),
            'dividend',
            $id
        );

        return response()->json([
            'success' => true,
            'message' => 'Dividend amount updated successfully.',
            'approved_amount' => number_format($dividend->approved_amount, 2),
        ]);
    }

    public function approve($id)
    {
        $this->ensureGeneralManager();

        $dividend = Dividend::findOrFail($id);

        if ($dividend->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending dividends can be approved.',
            ], 400);
        }

        $dividend->status = 'approved';
        $dividend->approved_at = now();
        $dividend->save();

        AuditLog::log(
            'Approved Dividend',
            "Approved dividend for {$dividend->user->first_name} {$dividend->user->last_name} (Year: {$dividend->year}) - ₱".number_format($dividend->approved_amount, 2),
            'dividend',
            $id
        );

        return response()->json([
            'success' => true,
            'message' => 'Dividend approved successfully.',
        ]);
    }

    public function approveAll(Request $request)
    {
        $this->ensureGeneralManager();

        $year = $request->get('year', now()->year);

        $pendingDividends = Dividend::where('year', $year)->where('status', 'pending')->get();

        if ($pendingDividends->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No pending dividends to approve.'], 400);
        }

        Dividend::where('year', $year)->where('status', 'pending')->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        AuditLog::log(
            'Approved All Dividends',
            'Bulk approved '.$pendingDividends->count()." pending dividends for year {$year}.",
            'dividend',
            null
        );

        return response()->json([
            'success' => true,
            'message' => $pendingDividends->count().' dividend(s) approved.',
            'approvedCount' => Dividend::where('year', $year)->where('status', 'approved')->count(),
        ]);
    }

    public function disburseOne(Request $request, $id)
    {
        $this->ensureGeneralManager();

        $dividend = Dividend::with('user')->findOrFail($id);
        $disbursementType = $request->get('disbursement_type', 'savings');

        if ($dividend->status === 'disbursed') {
            return response()->json([
                'success' => false,
                'message' => 'This dividend has already been disbursed.',
            ], 400);
        }

        if ($dividend->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Only approved dividends can be disbursed.',
            ], 400);
        }

        DB::beginTransaction();

        try {
            $dividend->status = 'disbursed';
            $dividend->disbursed_at = now();
            $dividend->save();

            if ($disbursementType === 'savings') {
                $savingsAccount = savings_account_tbl::firstOrCreate(
                    ['user_id' => $dividend->user_id],
                    [
                        'balance' => 0,
                        'status' => 'active',
                        'opened_at' => now(),
                    ]
                );

                $savingsAccount->balance += $dividend->approved_amount;
                $savingsAccount->save();

                savings_transaction_tbl::create([
                    'savings_account_id' => $savingsAccount->id,
                    'type' => 'deposit',
                    'amount' => $dividend->approved_amount,
                    'payment_method' => 'Dividend',
                    'balance_after' => $savingsAccount->balance,
                    'note' => "Dividend payout for year {$dividend->year}",
                    'reference_no' => 'DIV-'.$dividend->year.'-'.str_pad($dividend->id, 5, '0', STR_PAD_LEFT),
                    'transaction_date' => now()->toDateString(),
                    'status' => 'Completed',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } elseif ($disbursementType === 'share_capital') {
                $scAccount = share_capital_account_tbl::firstOrCreate(
                    ['user_id' => $dividend->user_id],
                    [
                        'total_shares' => 0,
                        'total_amount' => 0,
                        'status' => 'Active',
                    ]
                );

                $scAccount->total_amount += $dividend->approved_amount;
                $scAccount->save();
            }

            DB::commit();

            AuditLog::log(
                'Disbursed Dividend',
                "Disbursed dividend for {$dividend->user->first_name} {$dividend->user->last_name} (Year: {$dividend->year}) - ₱".number_format($dividend->approved_amount, 2)." to {$disbursementType}",
                'dividend',
                $id
            );

            $partialView = view('admin_components.dividends_table_partial', $this->getDividendData($request))->render();

            return response()->json([
                'success' => true,
                'message' => "Dividend disbursed successfully to {$dividend->user->first_name} {$dividend->user->last_name}.",
                'html' => $partialView,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Disbursement failed: '.$e->getMessage(),
            ], 500);
        }
    }

    public function disburseAll(Request $request, $year = null)
    {
        $this->ensureGeneralManager();

        $year = $year ?? $request->get('year', now()->year);
        $disbursementType = $request->get('disbursement_type', 'savings');
        $isAjax = $request->ajax();

        $approvedDividends = Dividend::where('year', $year)
            ->where('status', 'approved')
            ->get();

        if ($approvedDividends->isEmpty()) {
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => 'No approved dividends to disburse.'], 400);
            }

            return redirect()->back()->with('error', 'No approved dividends to disburse.');
        }

        DB::beginTransaction();

        try {
            foreach ($approvedDividends as $dividend) {
                $dividend->status = 'disbursed';
                $dividend->disbursed_at = now();
                $dividend->save();

                if ($disbursementType === 'savings') {
                    $savingsAccount = savings_account_tbl::firstOrCreate(
                        ['user_id' => $dividend->user_id],
                        [
                            'balance' => 0,
                            'status' => 'active',
                            'opened_at' => now(),
                        ]
                    );

                    $savingsAccount->balance += $dividend->approved_amount;
                    $savingsAccount->save();

                    savings_transaction_tbl::create([
                        'savings_account_id' => $savingsAccount->id,
                        'type' => 'deposit',
                        'amount' => $dividend->approved_amount,
                        'payment_method' => 'Dividend',
                        'balance_after' => $savingsAccount->balance,
                        'note' => "Dividend payout for year {$year}",
                        'reference_no' => 'DIV-'.$year.'-'.str_pad($dividend->id, 5, '0', STR_PAD_LEFT),
                        'transaction_date' => now()->toDateString(),
                        'status' => 'Completed',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } elseif ($disbursementType === 'share_capital') {
                    $scAccount = share_capital_account_tbl::firstOrCreate(
                        ['user_id' => $dividend->user_id],
                        [
                            'total_shares' => 0,
                            'total_amount' => 0,
                            'status' => 'Active',
                        ]
                    );

                    $scAccount->total_amount += $dividend->approved_amount;
                    $scAccount->save();
                }
            }

            DB::table('dividend_distributions')
                ->where('year', $year)
                ->update(['status' => 'released', 'updated_at' => now()]);

            DB::commit();

            $totalDisbursed = $approvedDividends->sum('approved_amount');
            AuditLog::log(
                'Disbursed Dividends',
                "Disbursed all approved dividends for year {$year} to {$disbursementType} accounts. Total: ₱".number_format($totalDisbursed, 2)." ({$approvedDividends->count()} members)",
                'dividend',
                null
            );

            if ($isAjax) {
                $partialView = view('admin_components.dividends_table_partial', $this->getDividendData($request))->render();
                $remainingApproved = Dividend::where('year', $year)->where('status', 'approved')->count();

                return response()->json([
                    'success' => true,
                    'message' => 'Successfully disbursed '.$approvedDividends->count().' dividend(s) for year '.$year.'.',
                    'disbursedCount' => $approvedDividends->count(),
                    'html' => $partialView,
                    'approvedCount' => $remainingApproved,
                    'distributionStatus' => 'released',
                ]);
            }

            return redirect()->route('dividends.index', ['year' => $year])
                ->with('success', 'All approved dividends have been disbursed successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => 'Disbursement failed: '.$e->getMessage()], 500);
            }

            return redirect()->back()
                ->with('error', 'Disbursement failed: '.$e->getMessage());
        }
    }

    // ─── Patronage Refund Methods ───────────────────────────────────────────

    public function updatePatronageRefund(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Invalid amount'], 422);
        }

        $record = PatronageRefundDistribution::findOrFail($id);

        if ($record->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Only pending patronage refunds can be edited.'], 400);
        }

        $oldAmount = $record->amount;
        $record->amount = $request->amount;
        $record->save();

        AuditLog::log(
            'Updated Patronage Refund',
            "Updated patronage refund for {$record->user->first_name} {$record->user->last_name} (Year: {$record->year}) from ₱".number_format($oldAmount, 2).' to ₱'.number_format($record->amount, 2),
            'patronage_refund',
            $id
        );

        return response()->json([
            'success' => true,
            'message' => 'Patronage refund amount updated successfully.',
            'amount' => number_format($record->amount, 2),
        ]);
    }

    public function approvePatronageRefund($id)
    {
        $record = PatronageRefundDistribution::findOrFail($id);

        if ($record->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending patronage refunds can be approved.',
            ], 400);
        }

        $record->status = 'approved';
        $record->approved_at = now();
        $record->save();

        AuditLog::log(
            'Approved Patronage Refund',
            "Approved patronage refund for {$record->user->first_name} {$record->user->last_name} (Year: {$record->year}) - ₱".number_format($record->amount, 2),
            'patronage_refund',
            $id
        );

        return response()->json([
            'success' => true,
            'message' => 'Patronage refund approved successfully.',
        ]);
    }

    public function approveAllPatronageRefunds(Request $request)
    {
        $year = $request->get('year', now()->year);

        $pending = PatronageRefundDistribution::where('year', $year)->where('status', 'pending')->get();

        if ($pending->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No pending patronage refunds to approve.'], 400);
        }

        PatronageRefundDistribution::where('year', $year)->where('status', 'pending')->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        AuditLog::log(
            'Approved All Patronage Refunds',
            'Bulk approved '.$pending->count()." pending patronage refunds for year {$year}.",
            'patronage_refund',
            null
        );

        return response()->json([
            'success' => true,
            'message' => $pending->count().' patronage refund(s) approved.',
        ]);
    }

    public function disbursePatronageRefundOne(Request $request, $id)
    {
        $record = PatronageRefundDistribution::with('user')->findOrFail($id);

        if ($record->status === 'disbursed') {
            return response()->json([
                'success' => false,
                'message' => 'This patronage refund has already been disbursed.',
            ], 400);
        }

        if ($record->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Only approved patronage refunds can be disbursed.',
            ], 400);
        }

        DB::beginTransaction();

        try {
            $record->status = 'disbursed';
            $record->disbursed_at = now();
            $record->save();

            $savingsAccount = savings_account_tbl::firstOrCreate(
                ['user_id' => $record->user_id],
                [
                    'balance' => 0,
                    'status' => 'active',
                    'opened_at' => now(),
                ]
            );

            $savingsAccount->balance += $record->amount;
            $savingsAccount->save();

            savings_transaction_tbl::create([
                'savings_account_id' => $savingsAccount->id,
                'type' => 'deposit',
                'amount' => $record->amount,
                'payment_method' => 'Patronage Refund',
                'balance_after' => $savingsAccount->balance,
                'note' => "Patronage refund for year {$record->year}",
                'reference_no' => 'PAT-'.$record->year.'-'.str_pad($record->id, 5, '0', STR_PAD_LEFT),
                'transaction_date' => now()->toDateString(),
                'status' => 'Completed',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            AuditLog::log(
                'Disbursed Patronage Refund',
                "Disbursed patronage refund for {$record->user->first_name} {$record->user->last_name} (Year: {$record->year}) - ₱".number_format($record->amount, 2).' to savings',
                'patronage_refund',
                $id
            );

            $partialView = view('admin_components.patronage_table_partial', $this->getDividendData($request))->render();

            return response()->json([
                'success' => true,
                'message' => "Patronage refund disbursed to {$record->user->first_name} {$record->user->last_name}.",
                'html' => $partialView,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Disbursement failed: '.$e->getMessage(),
            ], 500);
        }
    }

    public function disburseAllPatronageRefunds(Request $request, $year = null)
    {
        $year = $year ?? $request->get('year', now()->year);
        $isAjax = $request->ajax();

        $approvedRecords = PatronageRefundDistribution::where('year', $year)
            ->where('status', 'approved')
            ->get();

        if ($approvedRecords->isEmpty()) {
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => 'No approved patronage refunds to disburse.'], 400);
            }

            return redirect()->back()->with('error', 'No approved patronage refunds to disburse.');
        }

        DB::beginTransaction();

        try {
            foreach ($approvedRecords as $record) {
                $record->status = 'disbursed';
                $record->disbursed_at = now();
                $record->save();

                $savingsAccount = savings_account_tbl::firstOrCreate(
                    ['user_id' => $record->user_id],
                    [
                        'balance' => 0,
                        'status' => 'active',
                        'opened_at' => now(),
                    ]
                );

                $savingsAccount->balance += $record->amount;
                $savingsAccount->save();

                savings_transaction_tbl::create([
                    'savings_account_id' => $savingsAccount->id,
                    'type' => 'deposit',
                    'amount' => $record->amount,
                    'payment_method' => 'Patronage Refund',
                    'balance_after' => $savingsAccount->balance,
                    'note' => "Patronage refund for year {$year}",
                    'reference_no' => 'PAT-'.$year.'-'.str_pad($record->id, 5, '0', STR_PAD_LEFT),
                    'transaction_date' => now()->toDateString(),
                    'status' => 'Completed',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            $totalDisbursed = $approvedRecords->sum('amount');
            AuditLog::log(
                'Disbursed Patronage Refunds',
                "Disbursed all approved patronage refunds for year {$year}. Total: ₱".number_format($totalDisbursed, 2)." ({$approvedRecords->count()} members)",
                'patronage_refund',
                null
            );

            if ($isAjax) {
                $partialView = view('admin_components.patronage_table_partial', $this->getDividendData($request))->render();
                $remainingApproved = PatronageRefundDistribution::where('year', $year)->where('status', 'approved')->count();

                return response()->json([
                    'success' => true,
                    'message' => 'Successfully disbursed '.$approvedRecords->count().' patronage refund(s) for year '.$year.'.',
                    'disbursedCount' => $approvedRecords->count(),
                    'html' => $partialView,
                    'approvedCount' => $remainingApproved,
                ]);
            }

            return redirect()->route('dividends.index', ['year' => $year])
                ->with('success', 'All approved patronage refunds have been disbursed.');
        } catch (\Throwable $e) {
            DB::rollBack();
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => 'Disbursement failed: '.$e->getMessage()], 500);
            }

            return redirect()->back()
                ->with('error', 'Disbursement failed: '.$e->getMessage());
        }
    }

    public function disburseBoth(Request $request, $year = null)
    {
        $this->ensureGeneralManager();

        $year = $year ?? $request->get('year', now()->year);
        $isAjax = $request->ajax();

        $approvedDividends = Dividend::where('year', $year)->where('status', 'approved')->get();
        $approvedPatronage = PatronageRefundDistribution::where('year', $year)->where('status', 'approved')->get();

        if ($approvedDividends->isEmpty() && $approvedPatronage->isEmpty()) {
            $msg = 'No approved dividends or patronage refunds to disburse.';
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => $msg], 400);
            }

            return redirect()->back()->with('error', $msg);
        }

        DB::beginTransaction();

        try {
            // Disburse dividends
            foreach ($approvedDividends as $dividend) {
                $dividend->status = 'disbursed';
                $dividend->disbursed_at = now();
                $dividend->save();

                $savingsAccount = savings_account_tbl::firstOrCreate(
                    ['user_id' => $dividend->user_id],
                    ['balance' => 0, 'status' => 'active', 'opened_at' => now()]
                );

                $savingsAccount->balance += $dividend->approved_amount;
                $savingsAccount->save();

                savings_transaction_tbl::create([
                    'savings_account_id' => $savingsAccount->id,
                    'type' => 'deposit',
                    'amount' => $dividend->approved_amount,
                    'payment_method' => 'Dividend',
                    'balance_after' => $savingsAccount->balance,
                    'note' => "Dividend payout for year {$year}",
                    'reference_no' => 'DIV-'.$year.'-'.str_pad($dividend->id, 5, '0', STR_PAD_LEFT),
                    'transaction_date' => now()->toDateString(),
                    'status' => 'Completed',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Disburse patronage refunds (separate transactions)
            foreach ($approvedPatronage as $record) {
                $record->status = 'disbursed';
                $record->disbursed_at = now();
                $record->save();

                $savingsAccount = savings_account_tbl::firstOrCreate(
                    ['user_id' => $record->user_id],
                    ['balance' => 0, 'status' => 'active', 'opened_at' => now()]
                );

                $savingsAccount->balance += $record->amount;
                $savingsAccount->save();

                savings_transaction_tbl::create([
                    'savings_account_id' => $savingsAccount->id,
                    'type' => 'deposit',
                    'amount' => $record->amount,
                    'payment_method' => 'Patronage Refund',
                    'balance_after' => $savingsAccount->balance,
                    'note' => "Patronage refund for year {$year}",
                    'reference_no' => 'PAT-'.$year.'-'.str_pad($record->id, 5, '0', STR_PAD_LEFT),
                    'transaction_date' => now()->toDateString(),
                    'status' => 'Completed',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('dividend_distributions')
                ->where('year', $year)
                ->update(['status' => 'released', 'updated_at' => now()]);

            DB::commit();

            $totalDividends = $approvedDividends->sum('approved_amount');
            $totalPatronage = $approvedPatronage->sum('amount');
            AuditLog::log(
                'Disbursed Dividends and Patronage Refunds',
                'Disbursed both dividends (₱'.number_format($totalDividends, 2).", {$approvedDividends->count()} members) and patronage refunds (₱".number_format($totalPatronage, 2).", {$approvedPatronage->count()} members) for year {$year}.",
                'dividend',
                null
            );

            $dividendHtml = view('admin_components.dividends_table_partial', $this->getDividendData($request))->render();
            $patronageHtml = view('admin_components.patronage_table_partial', $this->getDividendData($request))->render();

            if ($isAjax) {
                return response()->json([
                    'success' => true,
                    'message' => "Disbursed {$approvedDividends->count()} dividend(s) and {$approvedPatronage->count()} patronage refund(s) for year {$year}.",
                    'dividendHtml' => $dividendHtml,
                    'patronageHtml' => $patronageHtml,
                ]);
            }

            return redirect()->route('dividends.index', ['year' => $year])
                ->with('success', 'Both dividends and patronage refunds have been disbursed successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => 'Disbursement failed: '.$e->getMessage()], 500);
            }

            return redirect()->back()
                ->with('error', 'Disbursement failed: '.$e->getMessage());
        }
    }

    // ─── Fund Percentage Settings ───────────────────────────────────────────

    public function updateFundPercentage(Request $request)
    {
        $this->ensureGeneralManager();

        $validator = Validator::make($request->all(), [
            'year' => 'required|integer|min:2000|max:2100',
            'dividend_fund_percentage' => 'required|numeric|min:1|max:99',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Invalid input.'], 422);
        }

        $year = $request->year;
        $percentage = round($request->dividend_fund_percentage, 2);

        // Auto-calculate patronage percentage if not provided
        $patronagePercentage = $request->has('patronage_fund_percentage')
            ? round($request->patronage_fund_percentage, 2)
            : round(100 - $percentage, 2);

        if (round($percentage + $patronagePercentage, 2) != 100) {
            return response()->json(['success' => false, 'message' => 'Fund percentages must total 100%.'], 422);
        }

        $distribution = DB::table('dividend_distributions')
            ->where('year', $year)
            ->first();

        if (! $distribution) {
            return response()->json(['success' => false, 'message' => 'No distribution found for this year.'], 404);
        }

        DB::beginTransaction();

        try {
            DividendSetting::updateOrCreate(
                ['year' => $year],
                [
                    'dividend_fund_percentage' => $percentage,
                    'patronage_fund_percentage' => $patronagePercentage,
                    'updated_by' => auth()->id(),
                ]
            );

            $netSurplus = $distribution->net_surplus;
            $totalStatutory = $distribution->reserve_fund + $distribution->education_fund + $distribution->community_fund + $distribution->optional_fund;
            $remainingSurplus = isset($distribution->remaining_surplus)
                ? (float) $distribution->remaining_surplus
                : round($netSurplus - $totalStatutory, 2);

            $dividendFundPct = $percentage / 100;
            $patronageFundPct = $patronagePercentage / 100;
            $newDividendPool = round($remainingSurplus * $dividendFundPct, 2);
            $newPatronageRefund = round($remainingSurplus * $patronageFundPct, 2);

            DB::table('dividend_distributions')->where('year', $year)->update([
                'dividend_pool' => $newDividendPool,
                'patronage_refund_pool' => $newPatronageRefund,
                'updated_at' => now(),
            ]);

            DB::commit();

            AuditLog::log(
                'Updated Fund Percentages',
                "Changed dividend fund to {$percentage}% and patronage fund to {$patronagePercentage}% for year {$year}.",
                'dividend',
                null
            );

            $totalSumApproved = Dividend::where('year', $year)->sum('approved_amount');

            return response()->json([
                'success' => true,
                'message' => 'Fund percentages updated and allocations recalculated. Regenerate patronage refunds to apply the new pool to member refunds.',
                'dividend_fund_percentage' => $percentage,
                'patronage_fund_percentage' => $patronagePercentage,
                'dividend_pool' => number_format($newDividendPool, 2),
                'patronage_refund_pool' => number_format($newPatronageRefund, 2),
                'total_approved' => number_format($totalSumApproved, 2),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'message' => 'Update failed: '.$e->getMessage()], 500);
        }
    }

    public function updatePatronageBasis(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'year' => 'required|integer|min:2000|max:2100',
            'patronage_basis' => 'required|string|in:total_repayment,net_repayment',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Invalid input.'], 422);
        }

        $year = $request->year;
        $basis = $request->patronage_basis;

        DividendSetting::updateOrCreate(
            ['year' => $year],
            [
                'patronage_basis' => $basis,
                'updated_by' => auth()->id(),
            ]
        );

        AuditLog::log(
            'Updated Patronage Basis',
            "Changed patronage calculation basis for year {$year} to: {$basis}",
            'dividend',
            null
        );

        return response()->json([
            'success' => true,
            'message' => 'Patronage basis updated to: '.str_replace('_', ' ', $basis),
            'patronage_basis' => $basis,
        ]);
    }

    public function patronageBreakdown($id)
    {
        $record = PatronageRefundDistribution::with('user')->findOrFail($id);

        $basis = DB::table('dividend_distributions')
            ->where('year', $record->year)
            ->value('patronage_basis')
            ?? (DividendSetting::where('year', $record->year)->value('patronage_basis') ?? 'total_repayment');

        $loanRepayments = DB::table('lending_repayments_tbls')
            ->join('lending_program_tbls', 'lending_repayments_tbls.lending_id', '=', 'lending_program_tbls.id')
            ->where('lending_repayments_tbls.user_id', $record->user_id)
            ->whereYear('lending_repayments_tbls.payment_date', $record->year)
            ->select(
                'lending_repayments_tbls.payment_date',
                'lending_repayments_tbls.amount_paid',
                'lending_repayments_tbls.principal_paid',
                'lending_repayments_tbls.interest_paid',
                'lending_repayments_tbls.service_fee_paid',
                'lending_repayments_tbls.late_fee',
                'lending_program_tbls.lending_type'
            )
            ->orderBy('lending_repayments_tbls.payment_date')
            ->get();

        $additionalRecords = DB::table('patronage_records_tbls')
            ->where('user_id', $record->user_id)
            ->where('year', $record->year)
            ->orderBy('created_at')
            ->get();

        $totalInterest = 0;
        $totalServiceFee = 0;
        $totalLateFee = 0;
        $totalLoanPatronage = 0;
        $fallbackCount = 0;

        $repayments = $loanRepayments->map(function ($r) use (&$totalInterest, &$totalServiceFee, &$totalLateFee, &$totalLoanPatronage, &$fallbackCount, $basis) {
            $interest = (float) $r->interest_paid;
            $serviceFee = (float) $r->service_fee_paid;
            $lateFee = (float) $r->late_fee;
            $isFallback = $r->interest_paid === null || $r->service_fee_paid === null;

            $patronage = $isFallback
                ? (float) $r->amount_paid
                : ($basis === 'net_repayment'
                    ? $interest + $serviceFee
                    : $interest + $serviceFee + $lateFee);

            $totalInterest += $interest;
            $totalServiceFee += $serviceFee;
            $totalLateFee += $lateFee;
            $totalLoanPatronage += $patronage;
            if ($isFallback) {
                $fallbackCount++;
            }

            return [
                'date' => $r->payment_date,
                'amount_paid' => $r->amount_paid,
                'principal_paid' => $r->principal_paid,
                'interest_paid' => $r->interest_paid,
                'service_fee_paid' => $r->service_fee_paid,
                'late_fee' => $r->late_fee,
                'loan_type' => $r->lending_type,
                'patronage' => round($patronage, 2),
                'is_fallback' => $isFallback,
            ];
        });

        $totalAdditionalPatronage = $additionalRecords->sum('amount');
        $totalPatronage = round($totalLoanPatronage + $totalAdditionalPatronage, 2);

        return response()->json([
            'success' => true,
            'basis' => $basis,
            'record' => [
                'member_name' => trim(($record->user->first_name ?? 'Unknown').' '.($record->user->last_name ?? '')),
                'year' => $record->year,
                'total_patronage' => $record->total_patronage,
                'allocation_ratio' => $record->allocation_ratio,
                'amount' => $record->amount,
                'status' => $record->status,
            ],
            'loan_repayments' => $repayments,
            'fallback_count' => $fallbackCount,
            'additional_records' => $additionalRecords->map(fn ($r) => [
                'source' => $r->source,
                'description' => $r->description,
                'amount' => $r->amount,
            ]),
            'totals' => [
                'loan_patronage' => round($totalLoanPatronage, 2),
                'interest' => round($totalInterest, 2),
                'service_fee' => round($totalServiceFee, 2),
                'late_fee' => round($totalLateFee, 2),
                'additional_patronage' => round($totalAdditionalPatronage, 2),
                'total_patronage' => $totalPatronage,
                'matches_record' => abs($totalPatronage - (float) $record->total_patronage) < 0.01,
            ],
        ]);
    }
}
