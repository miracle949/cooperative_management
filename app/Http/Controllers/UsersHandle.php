<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\dividend_rates_tbl;
use App\Models\educational_tbl;
use App\Models\Family_tbl;
use App\Models\lending_program_tbl;
use App\Models\lending_repayments_tbl;
use App\Models\Membergovern_ids_tbl;
use App\Models\Membervehi_tbl;
use App\Models\Otherinfo_tbl;
use App\Models\savings_account_tbl;
use App\Models\savings_transaction_tbl;
use App\Models\share_capital_account_tbl;
use App\Models\share_capital_transaction_tbl;
use App\Models\Users_tbl;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;

use Illuminate\Support\Facades\Storage;

class UsersHandle extends Controller
{
    private function isLockedOutStatus($user): bool
    {
        return in_array(strtolower((string) $user->status), ['inactive', 'awaiting_release', 'resigned'], true)
            || strtolower((string) $user->role) === 'inactive';
    }

    public function AccountStatusPing(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'authenticated' => false,
                'redirect' => route('login'),
            ]);
        }

        // Always read fresh from the DB — the session copy is stale.
        $user = Users_tbl::find(Auth::id());

        if (!$user) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->json([
                'authenticated' => false,
                'redirect' => route('login'),
            ]);
        }

        $rr = \App\Models\ResignationRequest_tbl::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->first();

        $state = [
            'status' => strtolower((string) $user->status),
            'role' => strtolower((string) $user->role),
            'rr_status' => $rr->status ?? null,
            'rr_released' => (bool) ($rr->is_released ?? false),
            'rr_release_d' => $rr->release_date ?? null,
        ];

        $redirect = null;
        $message = null;

        if (strtolower((string) $user->status) === 'reactivation_pending') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            $redirect = route('login');
            $message = 'Your reactivation request is under review.';
        } elseif ($this->isLockedOutStatus($user)) {
            // resigned / awaiting_release / inactive → read-only page
            $redirect = route('member.inactive');
            $message = 'Your resignation has been approved.';
        }

        return response()->json([
            'authenticated' => true,
            'fingerprint' => md5(json_encode($state)),
            'redirect' => $redirect,
            'message' => $message,
        ]);
    }

    public function applicationFormButton(Request $request, $id)
    {
        try {
            $targetUser = \App\Models\Users_tbl::find($id);
            abort_unless(
                (int) auth()->id() === (int) $id
                || ($targetUser && in_array(strtolower((string) $targetUser->role), ['pending', 'inactive'], true))
                || \App\Services\SoDGuard::actingAsStaff(),
                403
            );

            $request->validate([
                // users_tbls
                'fullname' => 'nullable|string|max:255',
                'username' => 'nullable|string|max:255',
                'email' => 'nullable|email|max:255',

                // otherinfo_tbls
                'date_of_birth' => 'nullable|string|max:255',
                'place_of_birth' => 'nullable|string|max:255',
                'contact_no' => 'nullable|string|max:255',
                'present_address' => 'nullable|string|max:255',
                'permanent_address' => 'nullable|string|max:255',
                'sex' => 'nullable|string|max:255',
                'civil_status' => 'nullable|string|max:255',
                'citizenship' => 'nullable|string|max:255',
                'height' => 'nullable|string|max:255',
                'weight' => 'nullable|string|max:255',
                'blood_type' => 'nullable|string|max:255',
                'skills' => 'nullable|string|max:255',

                // spouse_tbls
                'spouse_name' => 'nullable|string|max:255',
                'spouse_date_birth' => 'nullable|date',
                'spouse_place_birth' => 'nullable|string|max:255',
                'number_son' => 'nullable|string|max:255',
                'number_daughter' => 'nullable|string|max:255',
                'other_spec' => 'nullable|string|max:255',

                // membergovern_ids_tbls
                'sss_id' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
                'philhealth_id' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
                'pagibig_id' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
                'tin_id' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',

                // membervehi_tbls
                'uv_plate_no' => 'nullable|array',
                'uv_plate_no.*' => 'nullable|string',
                'taxi_plate_no' => 'nullable|array',
                'taxi_plate_no.*' => 'nullable|string',
                'bus_plate_no' => 'nullable|array',
                'bus_plate_no.*' => 'nullable|string',
                'mini_bus_plate_no' => 'nullable|array',
                'mini_bus_plate_no.*' => 'nullable|string',
                'jeep_plate_no' => 'nullable|array',
                'jeep_plate_no.*' => 'nullable|string',
                'multi_cab_plate_no' => 'nullable|array',
                'multi_cab_plate_no.*' => 'nullable|string',
                'tricycle_plate_no' => 'nullable|array',
                'tricycle_plate_no.*' => 'nullable|string',
                'total_uv' => 'nullable|integer|min:0',
                'total_taxi' => 'nullable|integer|min:0',
                'total_bus' => 'nullable|integer|min:0',
                'total_mini_bus' => 'nullable|integer|min:0',
                'total_jeep' => 'nullable|integer|min:0',
                'total_multi_cab' => 'nullable|integer|min:0',
                'total_tricycle' => 'nullable|integer|min:0',

                // educational_tbls
                'educational_level' => 'nullable|array',
                'educational_level.*' => 'nullable|string|max:255',
                'edu_status' => 'nullable|array',
                'edu_status.*' => 'nullable|string|max:255',
                'edu_specify' => 'nullable|array',
                'edu_specify.*' => 'nullable|string|max:255',
            ]);

            Users_tbl::where('id', $id)->update($request->only([
                'fullname',
                'username',
                'email',
            ]));

            Family_tbl::updateOrCreate(
                ['user_id' => $id],
                [
                    'spouse_name' => $request->spouse_name,
                    'spouse_date_birth' => $request->spouse_date_birth,
                    'spouse_place_birth' => $request->spouse_place_birth,
                    'number_son' => $request->number_son,
                    'number_daughter' => $request->number_daughter,
                    'other_spec' => $request->other_spec,
                ]
            );

            Otherinfo_tbl::updateOrCreate(
                ['user_id' => $id],
                [
                    'date_of_birth' => $request->date_of_birth,
                    'place_of_birth' => $request->place_of_birth,
                    'contact_no' => $request->contact_no,
                    'present_address' => $request->present_address,
                    'permanent_address' => $request->permanent_address,
                    'sex' => $request->sex,
                    'civil_status' => $request->civil_status,
                    'citizenship' => $request->citizenship,
                    'height' => $request->height,
                    'weight' => $request->weight,
                    'blood_type' => $request->blood_type,
                    'skills' => $request->skills,
                ]
            );

            $governmentData = [];
            $fileFields = ['sss_id', 'philhealth_id', 'pagibig_id', 'tin_id'];

            foreach ($fileFields as $field) {
                if ($request->hasFile($field)) {
                    $path = $request->file($field)->store('government_ids', 'public');
                    $governmentData[$field] = $path;
                }
            }

            Membergovern_ids_tbl::updateOrCreate(
                ['user_id' => $id],
                $governmentData
            );

            Membervehi_tbl::where('user_id', $id)->delete();

            $vehicleTypes = [
                'UV' => ['plate_name' => 'uv_plate_no', 'qty_name' => 'total_uv'],
                'TAXI' => ['plate_name' => 'taxi_plate_no', 'qty_name' => 'total_taxi'],
                'BUS' => ['plate_name' => 'bus_plate_no', 'qty_name' => 'total_bus'],
                'MINI BUS' => ['plate_name' => 'mini_bus_plate_no', 'qty_name' => 'total_mini_bus'],
                'JEEP' => ['plate_name' => 'jeep_plate_no', 'qty_name' => 'total_jeep'],
                'MULTI-CAB' => ['plate_name' => 'multi_cab_plate_no', 'qty_name' => 'total_multi_cab'],
                'TRICYCLE' => ['plate_name' => 'tricycle_plate_no', 'qty_name' => 'total_tricycle'],
            ];

            foreach ($vehicleTypes as $type => $fields) {
                $quantity = (int) $request->input($fields['qty_name'], 0);
                $plates = $request->input($fields['plate_name']);

                if ($quantity <= 0 || empty($plates) || !is_array($plates)) {
                    continue;
                }

                foreach ($plates as $plate_no) {
                    $plate_no = trim((string) ($plate_no ?? ''));
                    if ($plate_no === '') {
                        continue;
                    }

                    Membervehi_tbl::create([
                        'user_id' => $id,
                        'plate_no' => $plate_no,
                        'vehicle_type' => $type,
                        'quantity' => 1,
                    ]);
                }
            }

            $levels = ['Elementary', 'Secondary', 'Vocational/Trade Course', 'College'];

            foreach ($levels as $index => $level) {
                educational_tbl::updateOrCreate(
                    ['user_id' => $id, 'educational_level' => $level],
                    [
                        'status' => $request->edu_status[$index] ?? null,
                        'specify' => $request->edu_specify[$index] ?? null,
                    ]
                );
            }

            $user = Users_tbl::findOrFail($id);
            $vehicles = Membervehi_tbl::where('user_id', $id)->get()->groupBy('vehicle_type');
            $spouse = Family_tbl::where('user_id', $id)->first();
            $other = Otherinfo_tbl::where('user_id', $id)->first();
            $education = educational_tbl::where('user_id', $id)->get();
            $governmentIds = Membergovern_ids_tbl::where('user_id', $id)->first();

            AuditLog::log(
                'Updated Application Form',
                "Updated profile/application form for user #{$id}",
                'user',
                $id
            );

            return redirect()->route('applicationForm', $id)
                ->with('success', 'Application form submitted successfully!');

        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function MemberPortal(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')->withErrors(['login' => 'Please log in to continue.']);
        }

        if (method_exists($user, 'isMemberBased') && $user->isMemberBased()) {
            if (strtolower((string) $user->status) === 'inactive' || strtolower((string) $user->role) === 'inactive') {
                return redirect()->route('member.inactive');
            }

            if (strtolower((string) $user->status) === 'reactivation_pending') {
                auth()->logout();
                request()->session()->invalidate();
                request()->session()->regenerateToken();

                return redirect()->route('login')
                    ->withErrors(['login' => 'Your reactivation request is still under review.']);
            }
        }

        $member = $user->otherinfo;
        $username = $user->username ?? null;
        $email = $user->email ?? null;

        $firstName = $user->first_name ?? '';
        $middleName = $user->middle_name ?? '';
        $lastName = $user->last_name ?? '';

        // ── Year filter (drives all growth charts + announcements) ─────────────
        $currentYear = (int) Carbon::now()->year;
        $selectedYear = (int) $request->query('year', $currentYear);
        $availableYears = collect(range($currentYear, $currentYear - 4))->values();

        $referenceMonth = $selectedYear === $currentYear
            ? Carbon::now()
            : Carbon::create($selectedYear, 12, 1);

        // ── Savings ──────────────────────────────────────────────────────────────
        $savingsAccount = savings_account_tbl::where('user_id', $user->id)->first();

        if (!$savingsAccount) {
            $savingsAccount = savings_account_tbl::create([
                'user_id' => $user->id,
                'balance' => 0.00,
                'status' => 'active',
                'opened_at' => Carbon::today(),
            ]);
        }

        // ── Savings Growth (bar chart, same logic as Savings page) ──────────────────
        $growthStart = $referenceMonth->copy()->startOfMonth()->subMonths(5);
        $growthMonths = collect(range(5, 0))->map(fn($i) => $referenceMonth->copy()->subMonths($i));

        $growthTxs = savings_transaction_tbl::where('savings_account_id', $savingsAccount->id)
            ->where('transaction_date', '>=', $growthStart)
            ->whereIn('type', ['deposit', 'withdrawal'])
            ->get()
            ->groupBy(fn($tx) => Carbon::parse($tx->transaction_date)->format('Y-m'));

        $savingsGrowth = collect();
        foreach ($growthMonths as $month) {
            $key = $month->format('Y-m');
            $monthTxs = $growthTxs->get($key, collect());
            $net = $monthTxs->sum(fn($tx) => $tx->type === 'deposit' ? (float) $tx->amount : -(float) $tx->amount);

            $savingsGrowth->push([
                'label' => $month->format('M'),
                'net' => $net,
                'is_current' => $month->isSameMonth(Carbon::now()),
            ]);
        }

        $maxGrowth = $savingsGrowth->max(fn($m) => max($m['net'], 0)) ?: 1;

        $savingsGrowth = $savingsGrowth->map(function ($m) use ($maxGrowth) {
            $m['height_percent'] = $m['net'] > 0
                ? max(6, round(($m['net'] / $maxGrowth) * 78))
                : 4;

            return $m;
        });

        // ── Share Capital ─────────────────────────────────────────────────────────
        $shareCapitalAccount = DB::table('share_capital_account_tbls')
            ->where('user_id', $user->id)
            ->first();

        if ($shareCapitalAccount) {
            $shareCapitalBalance = DB::table('share_capital_transaction_tbls')
                ->where('share_capital_account_id', $shareCapitalAccount->id)
                ->whereIn('status', ['Completed', 'completed'])
                ->sum('total_amount') ?? 0;

            $shareCapitalShares = DB::table('share_capital_transaction_tbls')
                ->where('share_capital_account_id', $shareCapitalAccount->id)
                ->whereIn('status', ['Completed', 'completed'])
                ->sum('shares') ?? 0;
        } else {
            $shareCapitalBalance = 0;
            $shareCapitalShares = 0;
        }

        // ── Balance month filter (drives Account Balance pie chart) ──────────────
        $balanceMonth = $request->query('balance_month', Carbon::now()->format('Y-m'));
        $balanceDateCarbon = Carbon::createFromFormat('Y-m', $balanceMonth)->endOfMonth()->endOfDay();

        $availableBalanceMonths = collect(range(0, 11))
            ->map(fn($i) => Carbon::now()->copy()->subMonths($i)->format('Y-m'))
            ->values();

        // ── Balances "as of" the selected date (drives Account Balance pie chart) ──
        $shareCapitalBalanceAsOf = 0;
        if ($shareCapitalAccount) {
            $shareCapitalBalanceAsOf = DB::table('share_capital_transaction_tbls')
                ->where('share_capital_account_id', $shareCapitalAccount->id)
                ->where('transaction_date', '<=', $balanceDateCarbon)
                ->whereIn('status', ['Completed', 'completed'])
                ->get()
                ->sum(fn($tx) => in_array($tx->type, ['Deposit', 'Subscription'])
                    ? (float) $tx->total_amount
                    : -(float) $tx->total_amount);
        }

        $isCurrentMonth = $balanceDateCarbon->isSameMonth(Carbon::now()) && $balanceDateCarbon->isSameYear(Carbon::now());

        $savingsBalanceAsOf = $isCurrentMonth
            ? (float) $savingsAccount->balance
            : DB::table('savings_transaction_tbls')
                ->where('savings_account_id', $savingsAccount->id)
                ->where('transaction_date', '<=', $balanceDateCarbon)
                ->get()
                ->sum(fn($tx) => strtolower($tx->type) === 'deposit' ? (float) $tx->amount : -(float) $tx->amount);

        $loanBalanceAsOf = DB::table('lending_program_tbls')
            ->where('user_id', $user->id)
            ->where('status', 'Approved')
            ->where('created_at', '<=', $balanceDateCarbon)
            ->get()
            ->sum(function ($loan) use ($balanceDateCarbon) {
                $repaidByDate = DB::table('lending_repayments_tbls')
                    ->where('lending_id', $loan->id)
                    ->where('payment_date', '<=', $balanceDateCarbon)
                    ->sum('amount_paid');

                return max(0, (float) $loan->lending_amount - (float) $repaidByDate);
            });

        // Still used elsewhere on the page (current, not "as of")
        $loanBalance = $loanBalanceAsOf;

        // ── Dividends & Patronage Refunds (bottom dashboard panel) ────────────
        $recentDividends = collect();
        if (DB::getSchemaBuilder()->hasTable('dividends')) {
            $recentDividends = DB::table('dividends')
                ->where('user_id', $user->id)
                ->orderByDesc('created_at')
                ->limit(5)
                ->get()
                ->map(fn($d) => [
                    'label' => 'Dividend (' . $d->year . ')',
                    'amount' => (float) ($d->approved_amount ?? 0),
                    'status' => $d->status ?? 'Pending',
                    'date' => Carbon::parse($d->approved_at ?? $d->created_at)->format('M d, Y'),
                ]);
        }

        $recentPatronage = collect();
        if (DB::getSchemaBuilder()->hasTable('patronage_refund_distributions_tbls')) {
            $recentPatronage = DB::table('patronage_refund_distributions_tbls')
                ->where('user_id', $user->id)
                ->orderByDesc('created_at')
                ->limit(5)
                ->get()
                ->map(fn($p) => [
                    'label' => 'Patronage Refund',
                    'amount' => (float) $p->amount,
                    'status' => $p->status ?? 'Pending',
                    'date' => Carbon::parse($p->created_at)->format('M d, Y'),
                ]);
        }

        // ── Account Balance chart (Share Capital / Savings / Loan Balance) ───────
        $accountBalanceChart = collect([
            ['label' => 'Share Capital', 'value' => max(0, $shareCapitalBalanceAsOf), 'color' => 'gold'],
            ['label' => 'Savings', 'value' => max(0, $savingsBalanceAsOf), 'color' => 'blue'],
            ['label' => 'Loan Balance', 'value' => $loanBalanceAsOf, 'color' => 'coral'],
        ]);

        $maxBalance = $accountBalanceChart->max('value') ?: 1;

        $accountBalanceChart = $accountBalanceChart->map(function ($item) use ($maxBalance) {
            $item['height_percent'] = $item['value'] > 0
                ? max(6, round(($item['value'] / $maxBalance) * 78))
                : 4;

            return $item;
        });

        // ── Loans ─────────────────────────────────────────────────────────────────
        $loans = lending_program_tbl::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        $activeLoansCount = $loans->where('status', 'Approved')->count();

        // ── Loan status rows (holds the NEXT-INSTALLMENT due date, distinct from
        // lending_program_tbls.due_date which is the loan's final maturity date) ──
        $loanStatusByLoanId = DB::table('lending_status_tbls')
            ->whereIn('lending_id', $loans->pluck('id'))
            ->get()
            ->keyBy('lending_id');

        $loans = $loans->map(function ($loan) use ($loanStatusByLoanId) {
            $statusRow = $loanStatusByLoanId->get($loan->id);
            $loan->card_status = $this->computeLoanCardStatus($loan, $statusRow);

            return $loan;
        });

        $today = Carbon::today();
        $penalizedLoans = [];
        $totalLateFees = 0;

        $typeMapOverdue = [
            'Personal Lending' => 'Personal Loan',
            'Emergency Lending' => 'Emergency Loan',
            'Business Lending' => 'Business Loan',
            'Education Lending' => 'Education Loan',
        ];

        $overdueLoansDisplay = $loans->where('status', 'Approved')
            ->map(function ($loan) use ($loanStatusByLoanId) {
                $status = $loanStatusByLoanId->get($loan->id);
                $loan->effective_due_date = $status->due_date ?? $loan->due_date;
                $loan->status_row = $status;

                return $loan;
            })
            ->filter(fn($loan) => !empty($loan->effective_due_date) && Carbon::parse($loan->effective_due_date)->isPast())
            ->map(function ($loan) use ($typeMapOverdue, $today) {
                $displayType = $typeMapOverdue[$loan->lending_type] ?? $loan->lending_type;
                $dueDateCarbon = Carbon::parse($loan->effective_due_date);
                $status = $loan->status_row;

                $diff = $dueDateCarbon->diff($today);
                if ($diff->m > 0 || $diff->y > 0) {
                    $unitsOverdue = $diff->y * 12 + $diff->m;
                    $subtitle = $unitsOverdue . ' month' . ($unitsOverdue == 1 ? '' : 's') . ' overdue';
                } else {
                    $unitsOverdue = $diff->d;
                    $subtitle = $unitsOverdue . ' day' . ($unitsOverdue == 1 ? '' : 's') . ' overdue';
                }

                // ── Live 2%-of-monthly-installment penalty preview ──────────────
                // totalPayments/totalPaid come from lending_status_tbls when
                // available, same source loanStatus() uses.
                $totalPayments = (int) ($status->total_payments ?? 0);
                $totalPayment = (float) ($loan->total_payment ?? $loan->lending_amount);
                $monthlyDue = $totalPayments > 0 ? round($totalPayment / $totalPayments, 2) : 0;

                $alreadyPenalizedForThis = $status && $status->last_penalty_date
                    && Carbon::parse($status->last_penalty_date)->gte($dueDateCarbon);

                $penaltyPreview = $alreadyPenalizedForThis ? 0 : round($monthlyDue * 0.02, 2);

                return [
                    'loan_id' => $loan->id,
                    'sort_at' => $dueDateCarbon,
                    'icon' => 'coral',
                    'icon_fa' => 'fa-triangle-exclamation',
                    'title' => "{$displayType}",
                    'subtitle' => $subtitle,
                    'date_display' => $dueDateCarbon->format('M d, Y'),
                    'amount' => $penaltyPreview,
                ];
            })
            ->sortByDesc('sort_at')
            ->values();

        // ── Net Standing "as of" selected month (drives Net Standing modal) ──────
        $standingMonth = $request->query('standing_month', Carbon::now()->format('Y-m'));
        $standingDateCarbon = Carbon::createFromFormat('Y-m', $standingMonth)->endOfMonth()->endOfDay();

        $shareCapitalStandingAsOf = 0;
        if ($shareCapitalAccount) {
            $shareCapitalStandingAsOf = DB::table('share_capital_transaction_tbls')
                ->where('share_capital_account_id', $shareCapitalAccount->id)
                ->where('transaction_date', '<=', $standingDateCarbon)
                ->whereIn('status', ['Completed', 'completed'])
                ->get()
                ->sum(fn($tx) => in_array($tx->type, ['Deposit', 'Subscription'])
                    ? (float) $tx->total_amount
                    : -(float) $tx->total_amount);
        }

        $isStandingCurrentMonth = $standingDateCarbon->isSameMonth(Carbon::now()) && $standingDateCarbon->isSameYear(Carbon::now());

        $savingsStandingAsOf = $isStandingCurrentMonth
            ? (float) $savingsAccount->balance
            : DB::table('savings_transaction_tbls')
                ->where('savings_account_id', $savingsAccount->id)
                ->where('transaction_date', '<=', $standingDateCarbon)
                ->get()
                ->sum(fn($tx) => strtolower($tx->type) === 'deposit' ? (float) $tx->amount : -(float) $tx->amount);

        $loanStandingAsOf = DB::table('lending_program_tbls')
            ->where('user_id', $user->id)
            ->where('status', 'Approved')
            ->where('created_at', '<=', $standingDateCarbon)
            ->get()
            ->sum(function ($loan) use ($standingDateCarbon) {
                $repaidByDate = DB::table('lending_repayments_tbls')
                    ->where('lending_id', $loan->id)
                    ->where('payment_date', '<=', $standingDateCarbon)
                    ->sum('amount_paid');

                return max(0, (float) $loan->lending_amount - (float) $repaidByDate);
            });

        $netStandingAsOf = $shareCapitalStandingAsOf + $savingsStandingAsOf - $loanStandingAsOf;

        $overdueCount = count($penalizedLoans);

        // ── Net savings this month (Savings Balance card subtext) ────────────────
        $netSavingsThisMonth = DB::table('savings_transaction_tbls')
            ->where('savings_account_id', $savingsAccount->id)
            ->whereYear('transaction_date', Carbon::now()->year)
            ->whereMonth('transaction_date', Carbon::now()->month)
            ->get()
            ->sum(fn($tx) => strtolower($tx->type) === 'deposit' ? (float) $tx->amount : -(float) $tx->amount);

        // ── Net Standing (true total: Share Capital + Savings - Loan Balance) ────
        $netStandingTotal = $shareCapitalBalance + (float) $savingsAccount->balance - $loanBalance;

        // ── Next due date across active, non-overdue loans (Active Loans subtext) ──
        // Uses the NEXT-INSTALLMENT due date from lending_status_tbls when available,
        // falling back to the loan's own due_date (final maturity) otherwise — keeps
        // this in sync with the Upcoming Dues card/modal below and with Notifications.
        $nextDueLoan = $loans->where('status', 'Approved')
            ->map(function ($loan) use ($loanStatusByLoanId) {
                $loan->effective_due_date = $loanStatusByLoanId->get($loan->id)->due_date ?? $loan->due_date;

                return $loan;
            })
            ->filter(fn($loan) => !empty($loan->effective_due_date) && Carbon::parse($loan->effective_due_date)->isFuture())
            ->sortBy(fn($loan) => Carbon::parse($loan->effective_due_date))
            ->first();
        $nextDueDisplay = $nextDueLoan ? Carbon::parse($nextDueLoan->effective_due_date)->format('M d') : null;

        // ── Earliest overdue date (Overdue Loans subtext) ─────────────────────────
        $earliestOverdue = collect($penalizedLoans)->sortBy('due_date')->first();
        $earliestOverdueDisplay = $earliestOverdue ? Carbon::parse($earliestOverdue['due_date'])->format('M d') : null;

        // ── Share Capital Growth (net contributions over last 6 months) ─────────
        $scGrowthTxs = DB::table('share_capital_transaction_tbls')
            ->where('share_capital_account_id', $shareCapitalAccount->id ?? 0)
            ->where('transaction_date', '>=', $growthStart)
            ->whereIn('status', ['Completed', 'completed', 'Approved', 'approved'])
            ->get()
            ->groupBy(fn($tx) => Carbon::parse($tx->transaction_date)->format('Y-m'));

        $shareCapitalGrowth = collect();
        foreach ($growthMonths as $month) {
            $key = $month->format('Y-m');
            $monthTxs = $scGrowthTxs->get($key, collect());
            $net = $monthTxs->sum(fn($tx) => in_array($tx->type, ['Deposit', 'Subscription'])
                ? (float) $tx->total_amount
                : -(float) $tx->total_amount);

            $shareCapitalGrowth->push([
                'label' => $month->format('M'),
                'net' => $net,
                'is_current' => $month->isSameMonth(Carbon::now()),
            ]);
        }

        $maxScGrowth = $shareCapitalGrowth->max(fn($m) => max($m['net'], 0)) ?: 1;

        $shareCapitalGrowth = $shareCapitalGrowth->map(function ($m) use ($maxScGrowth) {
            $m['height_percent'] = $m['net'] > 0
                ? max(6, round(($m['net'] / $maxScGrowth) * 78))
                : 4;

            return $m;
        });

        // ── Loan Balance Growth (net repayments over last 6 months) ──────────────
        $loanGrowthTxs = DB::table('lending_repayments_tbls')
            ->where('user_id', $user->id)
            ->where('payment_date', '>=', $growthStart)
            ->get()
            ->groupBy(fn($tx) => Carbon::parse($tx->payment_date)->format('Y-m'));

        $loanBalanceGrowth = collect();
        foreach ($growthMonths as $month) {
            $key = $month->format('Y-m');
            $monthTxs = $loanGrowthTxs->get($key, collect());
            $net = $monthTxs->sum(fn($tx) => (float) $tx->amount_paid);

            $loanBalanceGrowth->push([
                'label' => $month->format('M'),
                'net' => $net,
                'is_current' => $month->isSameMonth(Carbon::now()),
            ]);
        }

        $maxLoanGrowth = $loanBalanceGrowth->max(fn($m) => max($m['net'], 0)) ?: 1;

        $loanBalanceGrowth = $loanBalanceGrowth->map(function ($m) use ($maxLoanGrowth) {
            $m['height_percent'] = $m['net'] > 0
                ? max(6, round(($m['net'] / $maxLoanGrowth) * 78))
                : 4;

            return $m;
        });

        // Dividend rate
        $dividendRateRecord = null;
        try {
            if (DB::getSchemaBuilder()->hasTable('dividend_rates_tbls')) {
                $dividendRateRecord = DB::table('dividend_rates_tbls')
                    ->orderByDesc('effective_year')
                    ->orderByDesc('created_at')
                    ->first();
            }
        } catch (\Throwable) {
        }
        $dividendRate = $dividendRateRecord->rate ?? 8.5;

        // Next dividend date
        $today2 = Carbon::today();
        $jun15ThisYear = Carbon::create($today2->year, 6, 15);
        $dec15ThisYear = Carbon::create($today2->year, 12, 15);
        $jun15NextYear = Carbon::create($today2->year + 1, 6, 15);

        if ($today2->lte($jun15ThisYear)) {
            $nextDividendDate = $jun15ThisYear;
        } elseif ($today2->lte($dec15ThisYear)) {
            $nextDividendDate = $dec15ThisYear;
        } else {
            $nextDividendDate = $jun15NextYear;
        }

        $announcements = \App\Models\Announcements_tbl::with(['user', 'comments.user', 'likes'])
            ->withCount('likes', 'comments')
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        $polls = \App\Models\AnnouncementPoll_tbl::with(['user', 'votes'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // ── Recent Transactions (dashboard preview, reuses Transactions page builders) ──
        $recentTransactions = collect()
            ->concat($this->buildShareCapitalEntries($user->id))
            ->concat($this->buildSavingsEntries($user->id))
            ->concat($this->buildLoanEntries($user->id))
            ->sortByDesc(fn($e) => $e['sort_at'])
            ->take(8)
            ->values();

        // ── Upcoming Dues (next payment per active loan) ─────────────────────────
        // Uses the NEXT-INSTALLMENT due date from lending_status_tbls (falls back to
        // the loan's own due_date/maturity if no status row exists yet), so this
        // matches what the Loan Application page and Notifications bell show.
        $typeMapDues = [
            'Personal Lending' => 'Personal Loan',
            'Emergency Lending' => 'Emergency Loan',
            'Business Lending' => 'Business Loan',
            'Education Lending' => 'Education Loan',
        ];

        $upcomingDues = collect();
        foreach ($loans->where('status', 'Approved') as $loan) {
            $statusRow = $loanStatusByLoanId->get($loan->id);
            $rawDueDate = $statusRow->due_date ?? $loan->due_date;

            if (empty($rawDueDate)) {
                continue;
            }

            $dueDateCarbon = Carbon::parse($rawDueDate);
            if ($dueDateCarbon->isPast()) {
                continue; // already overdue — handled separately by penalty logic
            }

            $termMonths = (int) filter_var($loan->lending_type_term, FILTER_SANITIZE_NUMBER_INT);
            $totalPayments = (int) ($statusRow->total_payments ?? $termMonths);
            $monthlyDue = $totalPayments > 0
                ? (float) $loan->lending_amount / $totalPayments
                : (float) $loan->lending_amount;

            $displayType = $typeMapDues[$loan->lending_type] ?? $loan->lending_type;

            $upcomingDues->push([
                'loan_id' => $loan->id,
                'sort_at' => $dueDateCarbon,
                'icon' => 'gold',
                'icon_fa' => 'fa-calendar-day',
                'title' => "{$displayType} Payment",
                'subtitle' => $this->formatDueIn(Carbon::today(), $dueDateCarbon),
                'date_display' => $dueDateCarbon->format('M d, Y'),
                'amount' => $monthlyDue,
            ]);
        }

        $upcomingDues = $upcomingDues->sortBy('sort_at')->values();

        // ── Seminars summary (drives the Seminars card + modal) ──────────────────
        // Simple attended/not-attended status only — no scheduling details here,
        // that belongs on the dedicated Seminars page.
        $coreSeminarTypes = ['pmes', 'fundamentals', 'finance'];

        $seminarTypeLabels = collect([
            'pmes' => 'PMES',
            'fundamentals' => 'Cooperative Fundamentals',
            'finance' => 'Cooperative Finance',
        ])->union(\App\Models\SeminarTypes_tbl::all()->pluck('label', 'slug'))->all();

        $seminarCompletion = \App\Models\SeminarCompletions_tbl::where('user_id', $user->id)->first();

        $seminarAttendeeRecords = \App\Models\SeminarAttendees_tbl::with('seminar')
            ->where('user_id', $user->id)
            ->get()
            ->filter(fn($a) => $a->seminar);

        // Core types use the completion table; custom types are complete once marked "attended"
        $seminarCompletedFlags = [];
        foreach ($seminarTypeLabels as $key => $label) {
            $seminarCompletedFlags[$key] = in_array($key, $coreSeminarTypes, true)
                ? (bool) ($seminarCompletion->{$key . '_completed'} ?? false)
                : $seminarAttendeeRecords->contains(fn($a) => $a->seminar->seminar_type === $key && $a->status === 'attended');
        }

        $seminarsSummary = collect($seminarTypeLabels)->map(function ($label, $key) use ($seminarCompletedFlags, $seminarAttendeeRecords) {
            $attendedSession = $seminarAttendeeRecords
                ->filter(fn($a) => $a->seminar->seminar_type === $key && $a->status === 'attended')
                ->sortByDesc(fn($a) => $a->seminar->schedule_datetime)
                ->first();

            $isCompleted = ($seminarCompletedFlags[$key] ?? false) || (bool) $attendedSession;

            return [
                'key' => $key,
                'label' => $label,
                'completed' => $isCompleted,
                'attended_datetime' => $attendedSession ? $attendedSession->seminar->schedule_datetime : null,
            ];
        })
            ->filter(fn($s) => $s['completed'])
            ->values();

        $upcomingSeminars = $seminarAttendeeRecords
            ->filter(function ($a) {
                if ($a->status !== 'pending') {
                    return false;
                }
                $sessionDate = Carbon::parse($a->seminar->schedule_datetime);

                return $sessionDate->isFuture() || $sessionDate->isToday();
            })
            ->sortBy(fn($a) => $a->seminar->schedule_datetime)
            ->map(function ($a) use ($seminarTypeLabels) {
                $s = $a->seminar;

                return [
                    'label' => $seminarTypeLabels[$s->seminar_type] ?? ucfirst($s->seminar_type),
                    'datetime' => Carbon::parse($s->schedule_datetime),
                    'delivery_type' => $s->delivery_type,
                    'meetup_place' => $s->meetup_place,
                ];
            })
            ->values();

        $scheduledTypes = $upcomingSeminars->pluck('label');

        $remainingUnscheduledSeminars = collect($seminarCompletedFlags)
            ->filter(fn($done) => !$done)
            ->keys()
            ->map(fn($key) => $seminarTypeLabels[$key] ?? ucfirst($key))
            ->reject(fn($label) => $scheduledTypes->contains($label))
            ->map(fn($label) => ['label' => $label])
            ->values();

        $seminarsCompletedCount = $seminarsSummary->count();
        $seminarsTotalCount = count($seminarTypeLabels);

        // Financial / Loan unlock depends on the 3 core seminars only, so a newly added type never locks members out
        $seminarsUnlocked = collect($coreSeminarTypes)->every(fn($k) => $seminarCompletedFlags[$k] ?? false);

        return view('members_components.member_portal', [
            'username' => $username,
            'email' => $email,
            'firstName' => $firstName,
            'middleName' => $middleName,
            'lastName' => $lastName,
            'member' => $member,

            // Savings
            'savingsAccount' => $savingsAccount,
            'savingsGrowth' => $savingsGrowth,

            'shareCapitalGrowth' => $shareCapitalGrowth,
            'loanBalanceGrowth' => $loanBalanceGrowth,

            // Transactions
            'recentTransactions' => $recentTransactions,

            // Upcoming Dues
            'upcomingDues' => $upcomingDues,

            // Share Capital
            'shareCapitalBalance' => $shareCapitalBalance,
            'shareCapitalShares' => $shareCapitalShares,
            'dividendRate' => $dividendRate,
            'nextDividendDate' => $nextDividendDate,

            // Account Balance chart
            'loanBalance' => $loanBalance,
            'accountBalanceChart' => $accountBalanceChart,

            // Announcements
            'announcements' => $announcements,

            // Polls
            'polls' => $polls,

            // Year filter
            'selectedYear' => $selectedYear,
            'availableYears' => $availableYears,

            'recentDividends' => $recentDividends,
            'recentPatronage' => $recentPatronage,

            'overdueLoansDisplay' => $overdueLoansDisplay,

            // Loans
            'loans' => $loans,
            'activeLoansCount' => $activeLoansCount,

            'balanceMonth' => $balanceMonth,
            'availableBalanceMonths' => $availableBalanceMonths,

            // Late Fee Penalties
            'penalizedLoans' => $penalizedLoans,
            'totalLateFees' => $totalLateFees,
            'overdueCount' => $overdueCount,

            'netSavingsThisMonth' => $netSavingsThisMonth,
            'netStandingTotal' => $netStandingTotal,
            'nextDueDisplay' => $nextDueDisplay,
            'earliestOverdueDisplay' => $earliestOverdueDisplay,

            // Net Standing modal
            'standingMonth' => $standingMonth,
            'shareCapitalStandingAsOf' => $shareCapitalStandingAsOf,
            'savingsStandingAsOf' => $savingsStandingAsOf,
            'loanStandingAsOf' => $loanStandingAsOf,
            'netStandingAsOf' => $netStandingAsOf,

            // Seminars
            'seminarsSummary' => $seminarsSummary,
            'upcomingSeminars' => $upcomingSeminars,
            'seminarsCompletedCount' => $seminarsCompletedCount,
            'seminarsTotalCount' => $seminarsTotalCount,
            'remainingUnscheduledSeminars' => $remainingUnscheduledSeminars,
            'seminarCompletedFlags' => $seminarCompletedFlags,
            'seminarTypeLabels' => $seminarTypeLabels,
            'seminarsUnlocked' => $seminarsUnlocked
        ]);
    }

    /**
     * Turns a target date into a human "Due in..." string, scaling the unit
     * automatically: days when close, months when further out, years+months
     * when very far out. Uses Carbon's calendar-accurate diff() so month
     * lengths (28/30/31 days) are handled correctly, not a flat ÷30.
     */
    private function formatDueIn(Carbon $today, Carbon $dueDate): string
    {
        if ($dueDate->isSameDay($today)) {
            return 'Due today';
        }

        $diff = $today->diff($dueDate); // calendar-accurate y/m/d breakdown

        // Years out (with leftover months, if any)
        if ($diff->y > 0) {
            $parts = [$diff->y . ' year' . ($diff->y === 1 ? '' : 's')];
            if ($diff->m > 0) {
                $parts[] = $diff->m . ' month' . ($diff->m === 1 ? '' : 's');
            }

            return 'Due in ' . implode(' ', $parts);
        }

        // Months out (with leftover days, if any)
        if ($diff->m > 0) {
            $parts = [$diff->m . ' month' . ($diff->m === 1 ? '' : 's')];
            if ($diff->d > 0) {
                $parts[] = $diff->d . ' day' . ($diff->d === 1 ? '' : 's');
            }

            return 'Due in ' . implode(' ', $parts);
        }

        // Just days out
        return $diff->d === 1 ? 'Due tomorrow' : "Due in {$diff->d} days";
    }

    private function toManila($value): Carbon
    {
        return Carbon::parse($value, config('app.timezone'))->timezone('Asia/Manila');
    }

    private function stampFields($value): array
    {
        $dt = $this->toManila($value);

        return [
            'sort_at' => $dt,
            'date_display' => $dt->format('M d, Y'),
            'time_display' => $dt->format('g:i A'),
        ];
    }

    // public function LoanApplication()
    // {

    //     $username = Auth::check() ? Auth::user()->username : null;
    //     $email = Auth::check() ? Auth::user()->email : null;

    //     return view(
    //         "members_components.loan_application",
    //         [
    //             "username" => $username,
    //             "email" => $email
    //         ]
    //     );
    // }

    public function LoanApplication()
    {
        $user = Auth::user();

        $username = Auth::check() ? Auth::user()->username : null;
        $email = Auth::check() ? Auth::user()->email : null;
        // $firstName = Auth::check() ? Auth::user()->first_name : null;

        $firstName = $user->first_name ?? '';

        $account = DB::table('share_capital_account_tbls')
            ->where('user_id', auth()->id())
            ->first();

        $currentShares = $account->total_shares ?? 0;
        $canApplyLoan = $currentShares >= 25;

        return view(
            'members_components.loan_application',
            [
                'username' => $username,
                'firstName' => $firstName,
                'email' => $email,
            ],
            compact('currentShares', 'canApplyLoan')
        );
    }

    public function ShareCapitalMember()
    {

        $username = Auth::check() ? Auth::user()->username : null;
        $email = Auth::check() ? Auth::user()->email : null;

        $memberId = Auth::id();

        $account = DB::table('share_capital_account_tbls')
            ->where('user_id', $memberId)
            ->first();

        if ($account) {
            $depositAmount = DB::table('share_capital_transaction_tbls')
                ->where('share_capital_account_id', $account->id)
                ->whereIn('type', ['Deposit', 'Subscription', ShareCapital::CONVERSION_TYPE])
                ->whereIn('status', ['Completed', 'completed'])
                ->sum('total_amount') ?? 0;

            $withdrawalAmount = DB::table('share_capital_transaction_tbls')
                ->where('share_capital_account_id', $account->id)
                ->where('type', 'Withdrawal')
                ->whereIn('status', ['Approved', 'approved'])
                ->sum('total_amount') ?? 0;

            $currentBalance = $depositAmount - $withdrawalAmount;

            $deposits = DB::table('share_capital_transaction_tbls')
                ->where('share_capital_account_id', $account->id)
                ->whereIn('type', ['Deposit', 'Subscription', ShareCapital::CONVERSION_TYPE])
                ->whereIn('status', ['Completed', 'completed'])
                ->sum('shares') ?? 0;

            $withdrawals = DB::table('share_capital_transaction_tbls')
                ->where('share_capital_account_id', $account->id)
                ->where('type', 'Withdrawal')
                ->whereIn('status', ['Approved', 'approved'])
                ->sum('shares') ?? 0;

            $currentShares = $deposits - $withdrawals;
        } else {
            $currentBalance = 0;
            $currentShares = 0;
        }

        // Fetch real contribution history
        $contributions = $account
            ? DB::table('share_capital_transaction_tbls')
                ->where('share_capital_account_id', $account->id)
                ->where('status', '!=', 'failed')
                ->orderBy('created_at', 'desc')
                ->get()
            : collect();

        return view(
            'members_components.share_capital',
            [
                'username' => $username,
                'email' => $email,
            ],
            compact('currentBalance', 'currentShares', 'contributions')
        );
    }

    public function Settings()
    {
        $user = Auth::user();
        $username = $user->username ?? null;
        $email = $user->email ?? null;
        $memberId = $user->id;

        $settings = \App\Models\AccountSettings_tbl::firstOrCreate(
            ['user_id' => $memberId],
            [
                'loan_reminders' => true,
                'savings_updates' => true,
                'email_digest' => false,
                'announcements' => true,
                'login_alerts' => true,
            ]
        );

        $passwordChangedAt = $user->password_changed_at
            ? Carbon::parse($user->password_changed_at)->diffForHumans()
            : 'Never changed';

        $pendingDeactivation = DB::table('resignation_requests_tbls')
            ->where('user_id', $memberId)
            ->where('status', 'Pending')
            ->exists();

        return view(
            'members_components.settings',
            [
                'username' => $username,
                'email' => $email,
                'settings' => $settings,
                'passwordChangedAt' => $passwordChangedAt,
                'pendingDeactivation' => $pendingDeactivation,
            ]
        );
    }

    public function UpdateSetting(Request $request)
    {
        $request->validate([
            'field' => 'required|string|in:loan_reminders,savings_updates,email_digest,announcements,login_alerts',
            'value' => 'required|boolean',
        ]);

        $memberId = Auth::id();
        $settings = \App\Models\AccountSettings_tbl::firstOrCreate(['user_id' => $memberId]);
        $settings->{$request->field} = $request->boolean('value');
        $settings->save();

        $user = Auth::user();
        AuditLog::log(
            'Updated Settings',
            "{$user->first_name} {$user->last_name} toggled {$request->field} " . ($request->boolean('value') ? 'on' : 'off'),
            'user',
            $memberId
        );

        return response()->json([
            'success' => true,
            'field' => $request->field,
            'value' => $settings->{$request->field},
        ]);
    }

    public function ChangePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        Auth::logoutOtherDevices($request->new_password);

        $user = Auth::user();

        Auth::logoutOtherDevices($request->new_password); // needs the AuthenticateSession middleware

        if (!\Illuminate\Support\Facades\Hash::check($request->current_password, $user->password)) {
            return response()->json(['success' => false, 'message' => 'Current password is incorrect.'], 422);
        }

        $user->password = bcrypt($request->new_password);
        $user->password_changed_at = now();
        $user->save();

        \App\Services\SingleSession::release($user->id);

        AuditLog::log(
            'Changed Password',
            "{$user->first_name} {$user->last_name} changed their password",
            'user',
            $user->id
        );

        return response()->json(['success' => true, 'message' => 'Password updated successfully.']);
    }

    public function forceLogoutMember($id)
    {
        $user = Users_tbl::findOrFail($id);

        \App\Services\SingleSession::release($user->id);

        AuditLog::log(
            'Forced Member Logout',
            "Released active session lock for {$user->first_name} {$user->last_name} (ID: {$id})",
            'user',
            $id
        );

        return redirect()->back()->with('success', 'Member session released. They can log in again.');
    }

    public function VerifyConversionPassword(Request $request)
    {
        $request->validate(['password' => 'required|string']);

        $user = Auth::user();

        if (!\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
            return response()->json(['success' => false, 'message' => 'Incorrect password. Please try again.'], 422);
        }

        // Valid for 2 minutes, one-time use (consumed by the convert endpoint)
        session(['convert_pw_verified_until' => now()->addMinutes(2)->timestamp]);

        return response()->json(['success' => true]);
    }

    public function RequestDeactivation(Request $request)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $memberId = Auth::id();

        $alreadyPending = DB::table('resignation_requests_tbls')
            ->where('user_id', $memberId)
            ->where('status', 'Pending')
            ->exists();

        if ($alreadyPending) {
            return response()->json([
                'success' => false,
                'message' => 'You already have a pending deactivation request.',
            ], 422);
        }

        DB::table('resignation_requests_tbls')->insert([
            'user_id' => $memberId,
            'reason' => $request->reason,
            'status' => 'Pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user = Auth::user();
        AuditLog::log(
            'Requested Deactivation',
            "{$user->first_name} {$user->last_name} requested account deactivation",
            'user',
            $memberId
        );

        return response()->json([
            'success' => true,
            'message' => 'Deactivation request submitted. Our staff will contact you.',
        ]);
    }

    public function ExportData()
    {
        $userId = Auth::id();

        $user = Users_tbl::find($userId);
        $otherinfo = Otherinfo_tbl::where('user_id', $userId)->first();
        $savingsAccount = savings_account_tbl::where('user_id', $userId)->first();
        $shareCapitalAccount = share_capital_account_tbl::where('user_id', $userId)->first();
        $loans = lending_program_tbl::where('user_id', $userId)->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.member_data', compact(
            'user',
            'otherinfo',
            'savingsAccount',
            'shareCapitalAccount',
            'loans'
        ));

        AuditLog::log(
            'Exported Data',
            "{$user->first_name} {$user->last_name} exported their membership data",
            'user',
            $userId
        );

        return $pdf->download('membership-record-' . $user->id . '.pdf');
    }

    public function Transactions(Request $request)
    {
        $username = Auth::check() ? Auth::user()->username : null;
        $email = Auth::check() ? Auth::user()->email : null;
        $memberId = Auth::id();

        // ── Filters from query string ──────────────────────────────
        $type = $request->query('type', 'all');           // all | share_capital | savings | loans
        $search = trim((string) $request->query('search', ''));
        $date = $request->query('date', '');
        $status = strtolower(trim((string) $request->query('status', 'all'))); // all | pending | completed | released | locked | credited | rejected | approved

        // if ($status === 'awaiting_release') {
        //     return redirect()->route('member.inactive');
        // }
        $page = max(1, (int) $request->query('page', 1));
        $perPage = 10;

        $entries = collect()
            ->concat($this->buildShareCapitalEntries($memberId))
            ->concat($this->buildSavingsEntries($memberId))
            ->concat($this->buildLoanEntries($memberId));

        // ── Summary cards (computed from ALL completed entries, unfiltered by tab/search) ──
        $completed = $entries->filter(fn($e) => ($e['status_class'] ?? '') === 'completed');

        $totalDeposits = $completed
            ->filter(fn($e) => in_array($e['category'], ['share_capital', 'savings']) && $e['amount'] > 0)
            ->sum('amount');

        $totalRepayments = $completed
            ->filter(fn($e) => !empty($e['is_repayment']))
            ->sum('amount');

        $thisMonth = $completed->filter(
            fn($e) => $e['sort_at']->isSameMonth(now('Asia/Manila')) && $e['sort_at']->isSameYear(now('Asia/Manila'))
        );
        $transactThisMonth = $thisMonth->sum(fn($e) => abs($e['amount']));

        $netChange = $completed->sum(fn($e) => !empty($e['is_repayment']) ? -$e['amount'] : $e['amount']);

        // ── Build the list of statuses actually present, for the dropdown ──
        // Uses status_label (the granular, human-facing value: "Locked", "Released",
        // "Credited", etc.) rather than status_class, which only ever holds
        // 'completed' or 'pending' and would collapse the dropdown to 2 options.
        $availableStatuses = $entries
            ->pluck('status_label')
            ->filter()
            ->map(fn($s) => trim($s))
            ->unique()
            ->sortBy(fn($s) => strtolower($s))
            ->values();

        // ── Apply date filter ──────────────────────────────────
        if ($date !== '') {
            $entries = $entries->filter(fn($e) => $e['sort_at']->format('Y-m-d') === $date);
        }

        // ── Apply tab filter ──────────────────────────────────────────
        if ($type !== 'all') {
            $entries = $entries->filter(fn($e) => $e['category'] === $type);
        }

        // ── Apply status filter (matches on the granular status_label, case-insensitive) ──
        if ($status !== 'all') {
            $entries = $entries->filter(fn($e) => strtolower($e['status_label'] ?? '') === $status);
        }

        // ── Apply search (description or reference no.) ────────────────
        if ($search !== '') {
            $needle = strtolower($search);
            $entries = $entries->filter(function ($e) use ($needle) {
                return str_contains(strtolower($e['title']), $needle)
                    || str_contains(strtolower($e['subtitle']), $needle)
                    || str_contains(strtolower($e['reference_no']), $needle);
            });
        }

        // ── Sort newest first, then paginate manually ───────────────────
        $entries = $entries->sortByDesc(fn($e) => $e['sort_at'])->values();
        $total = $entries->count();
        $paged = $entries->slice(($page - 1) * $perPage, $perPage)->values();

        $transactions = new \Illuminate\Pagination\LengthAwarePaginator(
            $paged,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view(
            'members_components.transactions',
            [
                'username' => $username,
                'email' => $email,
                'transactions' => $transactions,
                'type' => $type,
                'search' => $search,
                'date' => $date,
                'status' => $status,
                'availableStatuses' => $availableStatuses,
                'totalDeposits' => $totalDeposits,
                'totalRepayments' => $totalRepayments,
                'transactThisMonth' => $transactThisMonth,
                'netChange' => $netChange,
            ]
        );
    }

    // ─────────────────────────────────────────────────────────────────
    // Normalized transaction builders
    // ─────────────────────────────────────────────────────────────────

    private function buildShareCapitalEntries($memberId)
    {
        $account = DB::table('share_capital_account_tbls')->where('user_id', $memberId)->first();
        if (!$account) {
            return collect();
        }

        return DB::table('share_capital_transaction_tbls')
            ->where('share_capital_account_id', $account->id)
            ->get()
            ->map(function ($row) {
                $isDeposit = in_array($row->type, ['Deposit', 'Subscription', ShareCapital::CONVERSION_TYPE]);
                $statusRaw = strtolower($row->status ?? '');
                $statusClass = str_contains($statusRaw, 'complet') ? 'completed'
                    : (str_contains($statusRaw, 'pend') ? 'pending'
                        : (in_array($statusRaw, ['voided', 'void', 'rejected', 'declined', 'failed']) ? 'voided' : 'pending'));

                // Hide the amount until the deposit is approved/completed
                $hideAmount = $isDeposit && in_array($statusRaw, ['pending', ''], true);

                return [
                    'sort_at' => Carbon::parse($row->created_at ?? $row->transaction_date),
                    'category' => 'share_capital',
                    'icon' => 'gold',
                    'icon_fa' => 'fa-layer-group',
                    'title' => $isDeposit ? 'Share Capital Contribution' : 'Share Capital Withdrawal',
                    'subtitle' => $row->note ?: ($isDeposit ? 'Deposit' : 'Withdrawal request'),
                    'reference_no' => $row->reference_no ?? '—',
                    'date_display' => Carbon::parse($row->transaction_date)->format('M d, Y'),
                    'time_display' => Carbon::parse($row->created_at ?? $row->transaction_date)
                        ->timezone('Asia/Manila')->format('g:i A'),
                    'amount' => $isDeposit ? (float) $row->total_amount : -(float) $row->total_amount,
                    'show_amount' => !$hideAmount,
                    'status_label' => ucfirst($statusRaw ?: 'Pending'),
                    'status_class' => $statusClass,
                ];
            });
    }

    private function buildSavingsEntries($memberId)
    {
        $account = DB::table('savings_account_tbls')->where('user_id', $memberId)->first();
        if (!$account) {
            return collect();
        }

        return DB::table('savings_transaction_tbls')
            ->where('savings_account_id', $account->id)
            ->get()
            ->map(function ($row) {
                $config = match ($row->type) {
                    'deposit' => ['title' => 'Savings Deposit', 'subtitle' => 'Regular Savings', 'icon' => 'savings', 'icon_fa' => 'fa-piggy-bank', 'sign' => 1],
                    'withdrawal' => ['title' => 'Savings Withdrawal', 'subtitle' => 'Regular Savings', 'icon' => 'savings', 'icon_fa' => 'fa-piggy-bank', 'sign' => -1],
                    default => ['title' => ucfirst(str_replace('_', ' ', $row->type)), 'subtitle' => 'Savings activity', 'icon' => 'savings', 'icon_fa' => 'fa-piggy-bank', 'sign' => 1],
                };

                // Hide the amount while a deposit is still pending
                $hideAmount = $row->type === 'deposit'
                    && strtolower($row->status ?? 'completed') === 'pending';

                return [
                    'sort_at' => Carbon::parse($row->created_at ?? $row->transaction_date),
                    'category' => 'savings',
                    'icon' => $config['icon'],
                    'icon_fa' => $config['icon_fa'],
                    'title' => $config['title'],
                    'subtitle' => $row->note ?: $config['subtitle'],
                    'reference_no' => $row->reference_no ?? '—',
                    'date_display' => Carbon::parse($row->transaction_date)->format('M d, Y'),
                    'time_display' => Carbon::parse($row->created_at ?? $row->transaction_date)
                        ->timezone('Asia/Manila')->format('g:i A'),
                    'amount' => $config['sign'] * (float) $row->amount,
                    'show_amount' => !$hideAmount,
                    'status_label' => ucfirst($row->status ?? 'Completed'),
                    'status_class' => match (strtolower($row->status ?? 'completed')) {
                        'pending' => 'pending',
                        'voided', 'void', 'rejected', 'declined', 'failed' => 'voided',
                        default => 'completed',
                    },
                ];
            });
    }

    private function buildLoanEntries($memberId)
    {
        $typeMap = [
            'Personal Lending' => 'Personal Loan',
            'Emergency Lending' => 'Emergency Loan',
            'Business Lending' => 'Business Loan',
            'Education Lending' => 'Education Loan',
        ];

        $loans = DB::table('lending_program_tbls')
            ->where('user_id', $memberId)
            ->get();

        // 1) Loan Application — one entry per submitted application
        $applications = $loans->map(function ($row) use ($typeMap) {
            $displayType = $typeMap[$row->lending_type] ?? $row->lending_type;
            $statusRaw = strtolower($row->status ?? 'pending');
            $statusClass = match ($statusRaw) {
                'pending' => 'pending',
                'declined', 'rejected' => 'voided',
                default => 'completed',
            };

            return [
                'sort_at' => Carbon::parse($row->created_at),
                'category' => 'loans',
                'icon' => 'gold',
                'icon_fa' => 'fa-file-signature',
                'title' => 'Loan Application',
                'subtitle' => "{$displayType} application submitted",
                'reference_no' => $row->reference_no ?? '—',
                'date_display' => Carbon::parse($row->created_at)->format('M d, Y'),
                'time_display' => Carbon::parse($row->created_at)->timezone('Asia/Manila')->format('g:i A'),
                'amount' => 0,
                'show_amount' => false,
                'status_label' => ucfirst($row->status ?? 'Pending'),
                'status_class' => $statusClass,
            ];
        });

        // 2) Loan Approval / Decline — the decision on the application
        $decisions = $loans
            ->filter(fn($row) => in_array(strtolower($row->status ?? ''), ['approved', 'declined', 'rejected']))
            ->map(function ($row) use ($typeMap) {
                $displayType = $typeMap[$row->lending_type] ?? $row->lending_type;
                $isApproved = strtolower($row->status) === 'approved';

                return [
                    'sort_at' => Carbon::parse($row->updated_at ?? $row->created_at),
                    'category' => 'loans',
                    'icon' => $isApproved ? 'mint' : 'coral',
                    'icon_fa' => $isApproved ? 'fa-circle-check' : 'fa-circle-xmark',
                    'title' => $isApproved ? 'Loan Approved' : 'Loan Declined',
                    'subtitle' => $isApproved
                        ? "{$displayType} approved"
                        : trim("{$displayType} declined" . ($row->decline_reason ? " — {$row->decline_reason}" : '')),
                    'reference_no' => $row->reference_no ?? '—',
                    'date_display' => Carbon::parse($row->updated_at ?? $row->created_at)->format('M d, Y'),
                    'time_display' => Carbon::parse($row->updated_at ?? $row->created_at)->timezone('Asia/Manila')->format('g:i A'),
                    'amount' => 0,
                    'show_amount' => false,
                    'status_label' => $isApproved ? 'Approved' : 'Declined',
                    'status_class' => $isApproved ? 'completed' : 'voided',
                ];
            });

        // 3) Loan Disbursement — money actually released
        $disbursements = $loans
            ->filter(fn($row) => strtolower($row->status ?? '') === 'approved' && !empty($row->disbursed_at))
            ->map(function ($row) use ($typeMap) {
                $displayType = $typeMap[$row->lending_type] ?? $row->lending_type;

                return [
                    'sort_at' => Carbon::parse($row->disbursed_at),
                    'category' => 'loans',
                    'icon' => 'coral',
                    'icon_fa' => 'fa-file-invoice-dollar',
                    'title' => 'Loan Disbursement',
                    'subtitle' => "{$displayType} released" . ($row->disbursement_method ? " via {$row->disbursement_method}" : ''),
                    'reference_no' => $row->disbursement_reference ?? $row->reference_no ?? '—',
                    'date_display' => Carbon::parse($row->disbursed_at)->format('M d, Y'),
                    'time_display' => Carbon::parse($row->disbursed_at)->timezone('Asia/Manila')->format('g:i A'),
                    'amount' => (float) ($row->net_proceeds ?? $row->lending_amount),
                    'status_label' => 'Completed',
                    'status_class' => 'completed',
                ];
            });

        // 4) Loan Repayment — unchanged from before
        $repayments = DB::table('lending_repayments_tbls as r')
            ->leftJoin('lending_status_tbls as s', 's.lending_id', '=', 'r.lending_id')
            ->where('r.user_id', $memberId)
            ->select('r.*', 's.total_payments')
            ->get()
            ->map(function ($row) {
                $totalPayments = $row->total_payments ?? '?';

                return [
                    'sort_at' => Carbon::parse($row->created_at ?? $row->payment_date),
                    'category' => 'loans',
                    'icon' => 'mint',
                    'icon_fa' => 'fa-hand-holding-dollar',
                    'title' => 'Loan Repayment',
                    'subtitle' => "Installment {$row->payment_number} of {$totalPayments}",
                    'reference_no' => $row->reference_no ?? '—',
                    'date_display' => Carbon::parse($row->payment_date)->format('M d, Y'),
                    'time_display' => Carbon::parse($row->created_at ?? $row->payment_date)->timezone('Asia/Manila')->format('g:i A'),
                    'amount' => (float) $row->amount_paid,
                    'is_repayment' => true,
                    'status_label' => 'Completed',
                    'status_class' => 'completed',
                ];
            });

        return $applications->concat($decisions)->concat($disbursements)->concat($repayments);
    }

    public function Notifications()
    {
        $username = Auth::check() ? Auth::user()->username : null;
        $email = Auth::check() ? Auth::user()->email : null;
        $memberId = Auth::id();

        $notifications = \App\Models\Notifications_tbl::where('user_id', $memberId)
            ->orderBy('created_at', 'desc')
            ->get();

        $unreadCount = $notifications->where('is_read', false)->count();
        $importantCount = $notifications->where('is_important', true)->count();
        $inboxCount = $notifications->where('category', 'inbox')->count();
        $announcementCount = $notifications->where('category', 'announcement')->count();
        $spamCount = $notifications->where('category', 'spam')->count();
        $socialCount = $notifications->where('category', 'social')->count();

        // Group notifications into date buckets for display
        $grouped = $notifications->groupBy(function ($n) {
            $date = Carbon::parse($n->created_at);
            if ($date->isToday()) {
                return 'Today · ' . $date->format('M d, Y');
            } elseif ($date->isCurrentWeek()) {
                return 'Earlier this week';
            } elseif ($date->isCurrentMonth()) {
                return 'Earlier this month';
            }

            return $date->format('F Y');
        });

        return view(
            'members_components.notifications',
            [
                'username' => $username,
                'email' => $email,
                'notifications' => $notifications,
                'grouped' => $grouped,
                'unreadCount' => $unreadCount,
                'importantCount' => $importantCount,
                'inboxCount' => $inboxCount,
                'announcementCount' => $announcementCount,
                'spamCount' => $spamCount,
                'socialCount' => $socialCount,
            ]
        );
    }

    public function Faqs()
    {
        $username = Auth::check() ? Auth::user()->username : null;
        $email = Auth::check() ? Auth::user()->email : null;

        $faqs = \App\Models\Faq_tbl::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->groupBy('category')
            ->map(fn($items, $category) => [
                'category' => $category,
                'items' => $items->map(fn($f) => ['q' => $f->question, 'a' => $f->answer])->values()->all(),
            ])
            ->values()
            ->all();

        // The logged-in member's own reports only
        $myReports = \App\Models\SupportTicket_tbl::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();

        return view('members_components.faqs', compact('username', 'email', 'faqs', 'myReports'));
    }

    /**
     * Member-submitted support/appeal ticket — e.g. a rejected deposit, a
     * balance discrepancy, or a loan payment that didn't reflect. Purely
     * additive: does not touch any financial record.
     */
    public function SubmitReport(Request $request)
    {
        $request->validate([
            'category' => 'required|string|max:100',
            'category_other' => 'nullable|required_if:category,Other|string|max:80',
            'subject' => 'required|string|max:150',
            'message' => 'required|string|max:2000',
            'proof' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        // If they chose "Other", save what they typed, e.g. "Other - Wrong birthdate"
        $category = $request->category === 'Other'
            ? 'Other - ' . trim($request->category_other)
            : $request->category;

        $proofPath = $request->hasFile('proof')
            ? $request->file('proof')->store('support_tickets', 'public')
            : null;

        $ticket = \App\Models\SupportTicket_tbl::create([
            'user_id' => Auth::id(),
            'category' => $category,          // ← was $request->category
            'subject' => $request->subject,
            'message' => $request->message,
            'proof_path' => $proofPath,
            'status' => 'open',
        ]);

        $user = Auth::user();
        AuditLog::log(
            'Submitted Support Report',
            "{$user->first_name} {$user->last_name} submitted a report: {$request->subject}",
            'support_ticket',
            $ticket->id
        );

        return redirect()->back()->with('report_success', 'Your report has been submitted. Our team will get back to you shortly.');
    }

    /**
     * Member's own report/ticket history + status.
     */

    /**
     * Single source of truth for a loan's display status.
     * Used for both the list-grid badge and the hero pill so they
     * can never disagree again.
     */
    private function computeLoanCardStatus($loan, $statusRow = null): string
    {
        if (strtolower($loan->status) !== 'approved') {
            return ucfirst($loan->status); // Pending / Declined / etc.
        }

        // Fully paid off
        $totalPayments = (int) ($statusRow->total_payments ?? 0);
        $paymentsMade = (int) ($statusRow->payments_made ?? 0);
        if ($totalPayments > 0 && $paymentsMade >= $totalPayments) {
            return 'Completed';
        }

        // Overdue check — same rule the hero page uses
        $effectiveDueDate = $statusRow->due_date ?? $loan->due_date;
        if (!empty($effectiveDueDate) && Carbon::parse($effectiveDueDate)->isPast()) {
            return 'Overdue';
        }

        return 'Active';
    }

    public function MarkAllRead(Request $request)
    {
        $memberId = Auth::id();

        \App\Models\Notifications_tbl::where('user_id', $memberId)
            ->where('is_read', false)
            ->update(['is_read' => true, 'updated_at' => now()]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'All notifications marked as read.');
    }

    public function Financial(Request $request)
    {
        $user = Auth::user();
        $username = $user->username ?? null;
        $email = $user->email ?? null;
        $memberId = $user->id;

        $activeTab = $request->query('tab', 'savings'); // 'share_capital' | 'savings'

        $scController = new \App\Http\Controllers\ShareCapital;

        // ═══════════════════════════════════════════════════════════════
        // SHARE CAPITAL DATA  (same source logic as ShareCapital::memberIndex)
        // ═══════════════════════════════════════════════════════════════
        $account = DB::table('share_capital_account_tbls')
            ->where('user_id', $memberId)
            ->first();

        if ($account) {
            [$currentBalance, $currentShares] = $scController->computeBalanceAndShares($account);
        } else {
            $currentBalance = 0;
            $currentShares = 0;
        }

        $contributions = DB::table('share_capital_transaction_tbls')
            ->where('share_capital_account_id', $account->id ?? 0)
            ->where('status', '!=', 'failed')
            ->orderByDesc('transaction_date')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        $targetAmount = \App\Http\Controllers\ShareCapital::TARGET_AMOUNT;
        $targetShares = \App\Http\Controllers\ShareCapital::TARGET_SHARES;
        $parValue = \App\Http\Controllers\ShareCapital::PAR_VALUE;
        $paidUpPercent = $targetAmount > 0 ? min(100, round(($currentBalance / $targetAmount) * 100)) : 0;
        $remainingToTarget = max(0, $targetAmount - $currentBalance);
        $certificateEligible = $currentBalance >= $targetAmount;

        $timelineData = $scController->buildInstallmentTimeline($account, $contributions);
        $installmentTimeline = $timelineData['slots'];
        $advancePayment = $timelineData['advance'];
        $nextDueSlot = collect($installmentTimeline)->firstWhere('status', 'due')
            ?? collect($installmentTimeline)->firstWhere('status', 'upcoming');

        $dividendRateRecord = $scController->getDividendRateRecord();
        $dividendRate = $dividendRateRecord->rate ?? 8.5;
        $dividendRateYear = $dividendRateRecord->effective_year ?? now()->year;
        $rateHistory = $scController->getRateHistory();
        $dividendHistory = $account ? $scController->getDividendHistory($account->id) : collect();

        // Approximate Average Monthly Balance (AMB) as current paid-up balance,
        // same estimate used on the standalone Share Capital page.
        $averageMonthlyBalance = $currentBalance;
        $projectedNextDividend = round($averageMonthlyBalance * ($dividendRate / 100) / 2, 2);
        $iscAmount = round($projectedNextDividend * \App\Http\Controllers\ShareCapital::ISC_SPLIT, 2);
        $patronageAmount = round($projectedNextDividend * \App\Http\Controllers\ShareCapital::PATRONAGE_SPLIT, 2);

        // ═══════════════════════════════════════════════════════════════
        // SAVINGS DATA  (same source logic as SavingsController::index)
        // ═══════════════════════════════════════════════════════════════
        $ref = trim((string) $request->query('ref', ''));
        $date = $request->query('date', '');
        $status = strtolower(trim((string) $request->query('status', 'all')));
        $growthYear = (int) $request->query('growth_year', Carbon::now()->year);

        $savingsAccount = savings_account_tbl::where('user_id', $memberId)->first();

        if (!$savingsAccount) {
            $savingsAccount = savings_account_tbl::create([
                'user_id' => $memberId,
                'balance' => 0.00,
                'status' => 'active',
                'opened_at' => Carbon::today(),
            ]);
        }

        $regularSavingsBalance = (new \App\Http\Controllers\SavingsController)->computeSavingsBalance($savingsAccount->id);
        $timeDepositBalance = 0;
        $interestAccruedBalance = 0;
        $totalSavingsBalance = $regularSavingsBalance;

        $sirSettings = \App\Models\SavingsInterestSetting::getOrCreate();
        $regularSavingsRate = (float) $sirSettings->annual_rate;
        $regularSavingsFrequency = $sirSettings->frequency_label;

        // Interest building up this month, credited on the 1st of next month
        $now = Carbon::now();
        $estimatedQuarterInterest = round(
            $regularSavingsBalance * ($regularSavingsRate / 100) / max(1, (int) $sirSettings->frequency_divisor)
            * ($now->day / $now->daysInMonth),
            2
        );

        $availableGrowthYears = savings_transaction_tbl::where('savings_account_id', $savingsAccount->id)
            ->selectRaw('DISTINCT YEAR(transaction_date) as yr')
            ->pluck('yr')
            ->push(Carbon::now()->year)
            ->unique()
            ->sortDesc()
            ->values();

        $isCurrentYear = $growthYear === Carbon::now()->year;

        if ($isCurrentYear) {
            $growthStart = Carbon::now()->startOfMonth()->subMonths(5);
            $growthMonths = collect(range(5, 0))->map(fn($i) => Carbon::now()->subMonths($i));
        } else {
            $growthStart = Carbon::createFromDate($growthYear, 1, 1)->startOfMonth();
            $growthMonths = collect(range(0, 11))->map(fn($i) => Carbon::createFromDate($growthYear, 1, 1)->addMonths($i));
        }

        $growthTxs = savings_transaction_tbl::where('savings_account_id', $savingsAccount->id)
            ->where('transaction_date', '>=', $growthStart)
            ->when(!$isCurrentYear, fn($q) => $q->whereYear('transaction_date', $growthYear))
            ->whereIn('type', ['deposit', 'withdrawal'])
            ->get()
            ->groupBy(fn($tx) => Carbon::parse($tx->transaction_date)->format('Y-m'));

        $savingsGrowth = collect();
        foreach ($growthMonths as $month) {
            $key = $month->format('Y-m');
            $monthTxs = $growthTxs->get($key, collect());
            $net = $monthTxs->sum(fn($tx) => $tx->type === 'deposit' ? (float) $tx->amount : -(float) $tx->amount);

            $savingsGrowth->push([
                'label' => $month->format('M'),
                'net' => $net,
                'is_current' => $month->isSameMonth(Carbon::now()),
            ]);
        }

        $maxGrowth = $savingsGrowth->max(fn($m) => max($m['net'], 0)) ?: 1;
        $savingsGrowth = $savingsGrowth->map(function ($m) use ($maxGrowth) {
            $m['height_percent'] = $m['net'] > 0
                ? max(6, round(($m['net'] / $maxGrowth) * 78))
                : 4;

            return $m;
        });

        $tdHistory = collect();

        // Reuses the exact same rule the standalone Savings page uses to gate
        // Deposit/Withdraw behind an active Share Capital subscription.
        // $hasShareCapital = DB::table('share_capital_account_tbls')
        //     ->where('user_id', $memberId)
        //     ->where('status', 'Active')
        //     ->where('total_shares', '>', 0)
        //     ->exists();
        $hasShareCapital = $currentShares > 0;

        $type = $request->query('type', 'all');

        // ── Regular savings transactions ─────────────────────────────────
        $savingsTxs = savings_transaction_tbl::where('savings_account_id', $savingsAccount->id)
            ->whereIn('type', ['deposit', 'withdrawal', 'td_release', 'interest_credit'])
            ->when(
                in_array($type, ['deposit', 'withdrawal', 'td_release', 'interest_credit']),
                fn($q) => $q->where('type', $type)
            )
            ->when($ref !== '', fn($q) => $q->where('reference_no', 'like', '%' . $ref . '%'))
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->get();

        // ── Monthly interest releases (credited to the savings balance) ──
// Shown only under the "All" and "Interest Accrued" tabs, and only when
// the status filter is "All" or "Credited".
        $interestReleases = collect();

        if (in_array($type, ['all', 'interest_credit']) && in_array($status, ['all', 'credited'])) {
            // Skip any release that was also written into savings_transaction_tbls
            // as an interest_credit row, so it never shows twice.
            $existingInterestRefs = savings_transaction_tbl::where('savings_account_id', $savingsAccount->id)
                ->where('type', 'interest_credit')
                ->whereNotNull('reference_no')
                ->pluck('reference_no')
                ->all();

            $interestReleases = DB::table('savings_interest_releases_tbls')
                ->where('savings_account_id', $savingsAccount->id)
                ->when($ref !== '', fn($q) => $q->where('reference_no', 'like', '%' . $ref . '%'))
                ->get()
                ->reject(fn($r) => $r->reference_no && in_array($r->reference_no, $existingInterestRefs, true))
                ->map(fn($r) => (object) [
                    'id' => 'rel-' . $r->id,
                    'type' => 'interest_credit',
                    'reference_no' => $r->reference_no,
                    'transaction_date' => $r->created_at ?? $r->period_end,
                    'amount' => (float) $r->amount,
                    'status' => 'credited',
                    'payment_method' => null,
                    'gcash_reference_no' => null,
                    'void_reason' => null,
                    'period_label' => $r->period_label,
                    'created_at' => $r->created_at ?? $r->period_end,
                ]);
        }

        // ── Merge, sort newest first, paginate (10 per page) ─────────────
        $allTxs = $savingsTxs->concat($interestReleases)
            ->sortByDesc(fn($tx) =>
                Carbon::parse($tx->transaction_date)->format('Ymd')
                . Carbon::parse($tx->created_at ?? $tx->transaction_date)->format('YmdHis')
                . str_pad(is_numeric($tx->id) ? (string) $tx->id : '0', 12, '0', STR_PAD_LEFT))
            ->values();

        $perPage = 10;
        $page = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();

        $transactions = new \Illuminate\Pagination\LengthAwarePaginator(
            $allTxs->forPage($page, $perPage)->values(),
            $allTxs->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $totalMonths = savings_transaction_tbl::where('savings_account_id', $savingsAccount->id)
            ->groupByRaw("DATE_FORMAT(transaction_date, '%Y-%m')")
            ->count();

        $lastContribution = savings_transaction_tbl::where('savings_account_id', $savingsAccount->id)
            ->where('type', 'deposit')
            ->whereRaw('LOWER(status) = ?', ['completed'])
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();

        $lastContributionAmount = $lastContribution ? (float) $lastContribution->amount : 0;
        $lastContributionDate = $lastContribution
            ? Carbon::parse($lastContribution->transaction_date)->format('M d, Y')
            : 'No contributions yet';

        $totalContributions = savings_transaction_tbl::where('savings_account_id', $savingsAccount->id)
            ->where('type', 'deposit')
            ->whereRaw('LOWER(status) = ?', ['completed'])
            ->sum('amount');

        // ── Monthly Contribution Breakdown (feeds the Savings "Contribution
// Breakdown" modal — one grid of 12 months per available year) ──────────
        $monthlyBreakdownByYear = [];

        foreach ($availableGrowthYears as $yr) {
            $yearDeposits = savings_transaction_tbl::where('savings_account_id', $savingsAccount->id)
                ->where('type', 'deposit')
                ->whereRaw('LOWER(status) = ?', ['completed'])
                ->whereYear('transaction_date', $yr)
                ->get()
                ->groupBy(fn($tx) => Carbon::parse($tx->transaction_date)->format('n')); // month number, no leading zero

            $months = [];
            for ($m = 1; $m <= 12; $m++) {
                $monthTxs = $yearDeposits->get((string) $m, collect());
                $amount = $monthTxs->sum('amount');

                $months[] = [
                    'label' => Carbon::createFromDate($yr, $m, 1)->format('M'),
                    'paid' => $amount > 0,
                    'amount' => $amount,
                ];
            }

            $monthlyBreakdownByYear[$yr] = $months;
        }

        $lastUpdated = savings_transaction_tbl::where('savings_account_id', $savingsAccount->id)
            ->orderBy('transaction_date', 'desc')
            ->value('transaction_date');

        $lastUpdated = $lastUpdated
            ? Carbon::parse($lastUpdated)->diffForHumans()
            : 'No transactions yet';

        $monthsActive = (int) ceil(
            Carbon::parse($savingsAccount->opened_at)->floatDiffInMonths(Carbon::today())
        );

        $availableStatuses = savings_transaction_tbl::where('savings_account_id', $savingsAccount->id)
            ->whereNotNull('status')
            ->pluck('status')
            ->when(
                DB::table('savings_interest_releases_tbls')->where('savings_account_id', $savingsAccount->id)->exists(),
                fn($c) => $c->push('credited')
            )
            ->map(fn($s) => ucfirst($s))
            ->unique()
            ->sortBy(fn($s) => strtolower($s))
            ->values();

        // ═══════════════════════════════════════════════════════════════
        // SHARED
        // ═══════════════════════════════════════════════════════════════
        $gcashPaymentMethod = \App\Models\PaymentMethod::where('method_name', 'GCash')
            ->where('is_active', true)
            ->first();

        $paymentMethods = \App\Models\PaymentMethod::where('is_active', true)->orderBy('id')->get();

        return view('members_components.Financial', array_merge(
            ['username' => $username, 'email' => $email, 'activeTab' => $activeTab],
            compact(
                // Share Capital
                'currentBalance',
                'currentShares',
                'contributions',
                'targetAmount',
                'targetShares',
                'parValue',
                'paidUpPercent',
                'remainingToTarget',
                'certificateEligible',
                'installmentTimeline',
                'nextDueSlot',
                'advancePayment',
                'dividendRate',
                'dividendRateYear',
                'rateHistory',
                'dividendHistory',
                'averageMonthlyBalance',
                'projectedNextDividend',
                'iscAmount',
                'patronageAmount',
                // Savings
                'savingsAccount',
                'transactions',
                'type',
                'ref',
                'date',
                'status',
                'availableStatuses',
                'growthYear',
                'availableGrowthYears',
                'totalMonths',
                'lastContributionAmount',
                'lastContributionDate',
                'totalContributions',
                'monthlyBreakdownByYear',
                'lastUpdated',
                'monthsActive',
                'hasShareCapital',
                'regularSavingsBalance',
                'interestAccruedBalance',
                'totalSavingsBalance',
                'regularSavingsRate',
                'regularSavingsFrequency',
                'tdHistory',
                'savingsGrowth',
                'estimatedQuarterInterest',
                // Shared
                'gcashPaymentMethod',
                'paymentMethods'
            )
        ));
    }

    /**
     * AJAX helper for the member-side deposit forms. Returns whether a 13-digit
     * GCash reference number is already used by a NON-VOIDED transaction in any
     * of the three transaction tables. Messages only for the UI — the final
     * authority remains the server-side checks inside each store method.
     */
    public function checkReference(Request $request)
    {
        $ref = trim((string) $request->query('ref', ''));

        $used = $ref !== '' && preg_match('/^\d{13}$/', $ref) && (
            DB::table('share_capital_transaction_tbls')
                ->where('gcash_reference_no', $ref)
                ->where('status', '!=', 'voided')
                ->exists()
            || DB::table('savings_transaction_tbls')
                ->where('gcash_reference_no', $ref)
                ->where('status', '!=', 'voided')
                ->exists()
            || DB::table('lending_repayments_tbls')
                ->where('gcash_reference_no', $ref)
                ->where('status', '!=', 'voided')
                ->exists()
        );

        return response()->json(['used' => (bool) $used]);
    }

    public function Seminars()
    {
        $user = Auth::user();
        $username = $user->username ?? null;
        $email = $user->email ?? null;
        $userId = $user->id;

        $typeLabels = [
            'pmes' => 'PMES',
            'fundamentals' => 'Fundamentals of Coops',
            'finance' => 'Cooperative Finance',
        ];

        // ── Completion summary — moved up so it can filter Upcoming below ──
        $completion = \App\Models\SeminarCompletions_tbl::where('user_id', $userId)->first();
        $completedFlags = [
            'pmes' => (bool) ($completion->pmes_completed ?? false),
            'fundamentals' => (bool) ($completion->fundamentals_completed ?? false),
            'finance' => (bool) ($completion->finance_completed ?? false),
        ];
        $typeLabels = \App\Models\SeminarTypes_tbl::all()
            ->pluck('label', 'slug')
            ->union($typeLabels)
            ->all();

        // ── All of this member's attendee records, with their seminar info ──
        $attendeeRecords = \App\Models\SeminarAttendees_tbl::with('seminar')
            ->where('user_id', $userId)
            ->get()
            ->filter(fn($a) => $a->seminar); // guard against orphaned rows

        // ── Upcoming: scheduled sessions this member is registered for,
        // EXCLUDING any seminar type already marked attended/completed ──
        $upcomingSeminars = $attendeeRecords
            ->filter(fn($a) => $a->seminar->schedule_datetime >= now()
                && $a->status === 'pending'
                && !($completedFlags[$a->seminar->seminar_type] ?? false))
            ->sortBy(fn($a) => $a->seminar->schedule_datetime)
            ->map(function ($a) use ($typeLabels) {
                $s = $a->seminar;

                return [
                    'label' => $typeLabels[$s->seminar_type] ?? ucfirst($s->seminar_type),
                    'datetime' => $s->schedule_datetime,
                    'delivery_type' => $s->delivery_type,
                    'online_link' => $s->online_link,
                    'meetup_place' => $s->meetup_place,
                    'exact_venue' => $s->exact_venue,
                ];
            })
            ->values();

        // ── History: past sessions — attended, absent, or awaiting admin marking ──
        $seminarHistory = $attendeeRecords
            ->filter(fn($a) => $a->seminar->schedule_datetime < now())
            ->sortByDesc(fn($a) => $a->seminar->schedule_datetime)
            ->map(function ($a) use ($typeLabels) {
                $s = $a->seminar;

                $status = match ($a->status) {
                    'attended' => 'attended',
                    'absent' => 'missed',
                    default => 'pending_review', // date passed, admin hasn't marked yet
                };

                return [
                    'label' => $typeLabels[$s->seminar_type] ?? ucfirst($s->seminar_type),
                    'datetime' => $s->schedule_datetime,
                    'delivery_type' => $s->delivery_type,
                    'meetup_place' => $s->meetup_place,
                    'status' => $status,
                ];
            })
            ->values();

        $totalSeminars = count($typeLabels);
        // ── Completion summary (drives hero card) ──
        $completion = \App\Models\SeminarCompletions_tbl::where('user_id', $userId)->first();
        $completedFlags = [
            'pmes' => (bool) ($completion->pmes_completed ?? false),
            'fundamentals' => (bool) ($completion->fundamentals_completed ?? false),
            'finance' => (bool) ($completion->finance_completed ?? false),
        ];

        $totalSeminars = count($completedFlags);
        $completedCount = collect($completedFlags)->filter()->count();
        $remainingCount = $totalSeminars - $completedCount;
        $isFullyComplete = $remainingCount === 0;

        $remainingLabels = collect($completedFlags)
            ->filter(fn($done) => !$done)
            ->keys()
            ->map(fn($key) => $typeLabels[$key]);

        $nextUpcoming = $upcomingSeminars->first();

        if ($isFullyComplete) {
            $heroTitle = "You've completed full membership training!";
            $heroSubtitle = "You've finished all required seminars and are a full member in good standing.";
            $heroNextLine = null;
        } else {
            $heroTitle = $remainingCount === 1
                ? "You're one seminar away from full membership."
                : "Complete {$remainingCount} more seminars to deepen your understanding of the cooperative.";

            $heroSubtitle = $remainingCount === 1
                ? "Complete {$remainingLabels->first()} to finish the required track and unlock full member benefits."
                : 'Attend the required sessions below to unlock full member benefits and gain access to exclusive cooperative services, programs, and opportunities designed to support your financial growth.';

            $heroNextLine = null;
            if ($nextUpcoming) {
                $deliveryText = $nextUpcoming['delivery_type'] === 'online'
                    ? 'Online'
                    : 'F2F · ' . ($nextUpcoming['meetup_place'] ?? 'Venue TBA');

                $heroNextLine = "Next up: {$nextUpcoming['label']} · "
                    . $nextUpcoming['datetime']->format('M d') . ' · ' . $deliveryText;
            } else {
                $heroNextLine = 'Awaiting schedule from the cooperative.';
            }
        }

        $passcodeTypes = collect($completedFlags)
            ->filter(fn($done) => !$done)
            ->map(fn($done, $slug) => ['slug' => $slug, 'label' => $typeLabels[$slug]])
            ->values()
            ->all();

        return view('members_components.seminars', [
            'username' => $username,
            'email' => $email,
            'upcomingSeminars' => $upcomingSeminars,
            'seminarHistory' => $seminarHistory,
            'totalSeminars' => $totalSeminars,
            'completedCount' => $completedCount,
            'remainingCount' => $remainingCount,
            'isFullyComplete' => $isFullyComplete,
            'heroTitle' => $heroTitle,
            'heroSubtitle' => $heroSubtitle,
            'heroNextLine' => $heroNextLine,
            'passcodeTypes' => $passcodeTypes,
        ]);
    }

    public function verifySeminarPasscode(Request $request)
    {
        $coreTypes = ['pmes', 'fundamentals', 'finance'];
        $validTypes = array_merge($coreTypes, \App\Models\SeminarTypes_tbl::pluck('slug')->all());

        $request->validate([
            'seminar_type' => ['required', Rule::in($validTypes)],
            'passcode' => 'required|string|max:64',
        ]);

        $user = Auth::user();
        $type = $request->seminar_type;

        // Small helper: send the member back to the page they came from,
        // with the error shown under the passcode box.
        $fail = function (string $message) {
            return redirect()->back()
                ->withErrors(['passcode' => $message])
                ->withInput()
                ->with('error', $message);
        };

        // ── 1) Must be scheduled (an attendee) for a seminar of this type ──
        // An attendee record marked "absent" does not count as scheduled.
        $isScheduled = \App\Models\SeminarAttendees_tbl::where('user_id', $user->id)
            ->where('status', '!=', 'absent')
            ->whereHas('seminar', fn($q) => $q->where('seminar_type', $type))
            ->exists();

        if (!$isScheduled) {
            return $fail('You are not scheduled for this seminar.');
        }

        // ── 2) Passcode checks ──
        $passcode = \App\Models\SeminarPasscodes_tbl::where('seminar_type', $type)->first();

        if (!$passcode) {
            return $fail('No passcode has been set for this seminar yet.');
        }

        if ($passcode->expires_at && now()->gt($passcode->expires_at)) {
            return $fail('This passcode has already expired.');
        }

        if (!hash_equals((string) $passcode->passcode, (string) $request->passcode)) {
            return $fail('The passcode you entered is incorrect.');
        }

        // Custom seminar types have no completion column: mark the attendee record as attended
        if (!in_array($type, $coreTypes, true)) {
            $attendee = \App\Models\SeminarAttendees_tbl::where('user_id', $user->id)
                ->where('status', '!=', 'absent')
                ->whereHas('seminar', fn($q) => $q->where('seminar_type', $type))
                ->orderByDesc('id')
                ->first();

            if ($attendee->status === 'attended') {
                return $fail('You have already completed this seminar.');
            }

            $attendee->status = 'attended';
            $attendee->save();

            \App\Models\AuditLog::log(
                'Verified Seminar Passcode',
                "User #{$user->id} verified passcode for {$type}",
                'seminar_passcode',
                $user->id
            );

            return redirect()->back()
                ->with('success', 'Passcode accepted! Seminar marked as completed.')
                ->with('seminar_success', 'Passcode accepted! Seminar marked as completed.');
        }

        // ── 3) Mark complete ──
        $completion = \App\Models\SeminarCompletions_tbl::firstOrCreate(
            ['user_id' => $user->id],
            [
                'pmes_completed' => false,
                'fundamentals_completed' => false,
                'finance_completed' => false,
            ]
        );

        $column = $type . '_completed';
        if ($completion->$column) {
            return $fail('You have already completed this seminar.');
        }

        $completion->$column = true;
        $completion->save();

        \App\Http\Controllers\SeminarController::autoUpgradeIfComplete($user->id, $completion);

        \App\Models\AuditLog::log(
            'Verified Seminar Passcode',
            "User #{$user->id} verified passcode for {$type}",
            'seminar_passcode',
            $user->id
        );

        return redirect()->back()
            ->with('success', 'Passcode accepted! Seminar marked as completed.')
            ->with('seminar_success', 'Passcode accepted! Seminar marked as completed.');
    }

    public function ProfileMember()
    {
        $userId = Auth::id();

        $user = Users_tbl::find($userId);
        $otherinfo = Otherinfo_tbl::where('user_id', $userId)->first();
        $membergovernIds = Membergovern_ids_tbl::where('user_id', $userId)->first();
        $family = Family_tbl::where('user_id', $userId)->first();
        $vehicles = Membervehi_tbl::where('user_id', $userId)->get();
        $educational = educational_tbl::where('user_id', $userId)->first();
        $savingsAccount = savings_account_tbl::where('user_id', $userId)->first();
        $shareCapitalAccount = share_capital_account_tbl::where('user_id', $userId)->first();
        $dividendRate = dividend_rates_tbl::orderBy('effective_year', 'desc')->first();

        $savingsAccountId = $savingsAccount->id ?? null;
        $shareCapitalAccountId = $shareCapitalAccount->id ?? null;

        $savingsTransactions = collect();
        if ($savingsAccountId) {
            $savingsTransactions = savings_transaction_tbl::where('savings_account_id', $savingsAccountId)
                ->orderBy('transaction_date', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($item) {
                    return [
                        'date' => $item->transaction_date,
                        'type' => ucfirst($item->type),
                        'description' => 'Regular Savings - ' . ucfirst($item->type),
                        'amount' => $item->type === 'deposit' ? $item->amount : -$item->amount,
                        'status' => $item->status ?? 'Completed',
                    ];
                });
        }

        // ── Loan data for Account Balance & Repayment Progress ──────────────
        $typeMap = [
            'Personal Lending' => 'Personal Loan',
            'Emergency Lending' => 'Emergency Loan',
            'Business Lending' => 'Business Loan',
            'Education Lending' => 'Education Loan',
        ];

        $approvedLoans = DB::table('lending_program_tbls as l')
            ->leftJoin('lending_status_tbls as s', 's.lending_id', '=', 'l.id')
            ->where('l.user_id', $userId)
            ->where('l.status', 'Approved')
            ->select('l.*', 's.remaining_balance', 's.total_payments', 's.payments_made')
            ->get()
            ->map(function ($loan) use ($typeMap) {
                $loan->display_type = $typeMap[$loan->lending_type] ?? $loan->lending_type;
                $totalPayments = (int) ($loan->total_payments ?? 0);
                $paymentsMade = (int) ($loan->payments_made ?? 0);
                $loan->progress_percent = $totalPayments > 0
                    ? min(100, round(($paymentsMade / $totalPayments) * 100))
                    : 0;
                $loan->remaining_balance = (float) ($loan->remaining_balance ?? $loan->total_payment ?? $loan->lending_amount);

                return $loan;
            });

        // Total outstanding across ALL active loans → feeds "Loan Balance" stat
        $loanBalance = $approvedLoans->sum('remaining_balance');

        // Grouped by type → feeds "Loan Repayment Progress" panel.
        // Always show all 4 loan types, even ones the member has never taken —
        // zero balance / zero progress rather than omitting the row entirely.
        $loansByTypeActual = $approvedLoans->groupBy('display_type')->map(function ($loans) {
            return [
                'balance' => $loans->sum('remaining_balance'),
                'progress' => (int) round($loans->avg('progress_percent')),
            ];
        });

        $allLoanTypes = ['Personal Loan', 'Emergency Loan', 'Business Loan', 'Education Loan'];
        $loansByType = collect($allLoanTypes)->mapWithKeys(function ($type) use ($loansByTypeActual) {
            return [$type => $loansByTypeActual[$type] ?? ['balance' => 0, 'progress' => 0]];
        });

        $savedMonthlyIncome = $otherinfo->monthly_income ?? DB::table('lending_program_tbls')
            ->where('user_id', $userId)
            ->whereNotNull('monthly_income')
            ->orderBy('created_at', 'desc')
            ->value('monthly_income');

        $shareCapitalBalance = (float) ($shareCapitalAccount->total_amount ?? 0);
        $savingsBalance = (float) ($savingsAccount->balance ?? 0);
        // Overall = straight sum of all three balances shown in the Account Balance
        // card, not netted against the loan — the card lists Loan Balance as its
        // own line, so this total is a "what's on this card" sum, not net worth.
        $overallBalance = $shareCapitalBalance + $savingsBalance + $loanBalance;

        $shareCapitalTransactions = collect();
        if ($shareCapitalAccountId) {
            $shareCapitalTransactions = share_capital_transaction_tbl::where('share_capital_account_id', $shareCapitalAccountId)
                ->orderBy('transaction_date', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($item) {
                    return [
                        'date' => $item->transaction_date,
                        'type' => ucfirst($item->type),
                        'description' => 'Share Capital - ' . ucfirst($item->type),
                        'amount' => in_array($item->type, ['Subscription', 'Deposit', ShareCapital::CONVERSION_TYPE]) ? $item->total_amount : -$item->total_amount,
                        'status' => $item->status ?? 'Completed',
                    ];
                });
        }

        $loanRepayments = lending_repayments_tbl::where('user_id', $userId)
            ->orderBy('payment_date', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->payment_date,
                    'type' => 'Loan payment',
                    'description' => 'Loan Repayment',
                    'amount' => -$item->amount_paid,
                    'status' => 'Completed',
                ];
            });

        $transactions = $savingsTransactions
            ->concat($shareCapitalTransactions)
            ->concat($loanRepayments)
            ->sortByDesc('date')
            ->take(20)
            ->values();

        $memberSince = $user->created_at->format('F Y');

        $missingCount = 0;
        if ($otherinfo && empty($otherinfo->contact_no)) {
            $missingCount++;
        }
        if ($otherinfo && empty($otherinfo->present_address)) {
            $missingCount++;
        }
        if ($otherinfo && empty($otherinfo->permanent_address)) {
            $missingCount++;
        }
        if ($otherinfo && empty($otherinfo->date_of_birth)) {
            $missingCount++;
        }
        if ($otherinfo && empty($otherinfo->place_of_birth)) {
            $missingCount++;
        }
        if ($otherinfo && empty($otherinfo->sex)) {
            $missingCount++;
        }
        if ($otherinfo && empty($otherinfo->civil_status)) {
            $missingCount++;
        }
        if ($otherinfo && empty($otherinfo->citizenship)) {
            $missingCount++;
        }
        if ($otherinfo && empty($otherinfo->blood_type)) {
            $missingCount++;
        }
        if ($otherinfo && empty($otherinfo->height)) {
            $missingCount++;
        }
        if ($otherinfo && empty($otherinfo->weight)) {
            $missingCount++;
        }
        if ($membergovernIds && empty($membergovernIds->sss_id)) {
            $missingCount++;
        }
        if ($membergovernIds && empty($membergovernIds->philhealth_id)) {
            $missingCount++;
        }
        if ($membergovernIds && empty($membergovernIds->pagibig_id)) {
            $missingCount++;
        }
        if ($membergovernIds && empty($membergovernIds->tin_id)) {
            $missingCount++;
        }

        // ── Data for the "Print SOA" button on the profile page ──────────────
        $soaScStats = $shareCapitalAccount
            ? \App\Http\Controllers\ShareCapital::paidUpForAccount($shareCapitalAccount->id)
            : ['shares' => 0, 'amount' => 0];

        $soaSavingsBalance = $savingsAccount
            ? (new \App\Http\Controllers\SavingsController)->computeSavingsBalance($savingsAccount->id)
            : 0;

        $soaLoans = DB::table('lending_program_tbls')
            ->where('user_id', $userId)
            ->where('status', 'Approved')
            ->get()
            ->map(fn($l) => [
                'reference_no' => $l->reference_no,
                'lending_type' => $l->lending_type,
                'lending_amount' => (float) $l->lending_amount,
                'monthly_payment' => (float) $l->monthly_payment,
                'total_payment' => (float) $l->total_payment,
            ])->values()->all();

        $soaData = [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'contact_no' => $otherinfo->contact_no ?? '',
            'present_address' => $otherinfo->present_address ?? '',
            'membership_category' => $otherinfo->membership_category ?? 'Investor Associate',
            'created_at' => optional($user->created_at)->toDateTimeString(),
            'sc_total_shares' => $soaScStats['shares'],
            'sc_total_amount' => $soaScStats['amount'],
            'sc_status' => $shareCapitalAccount->status ?? 'No Account',
            'savings_balance' => $soaSavingsBalance,
            'savings_status' => $savingsAccount->status ?? 'No Account',
            'savings_interest' => (float) ($savingsAccount->interest_accrued_balance ?? 0),
            'active_loans' => $soaLoans,
        ];

        return view(
            'members_components.profile',
            [
                'user' => $user,
                'otherinfo' => $otherinfo,
                'membergovernIds' => $membergovernIds,
                'family' => $family,
                'vehicles' => $vehicles,
                'educational' => $educational,
                'savingsAccount' => $savingsAccount,
                'shareCapitalAccount' => $shareCapitalAccount,
                'dividendRate' => $dividendRate,
                'transactions' => $transactions,
                'memberSince' => $memberSince,
                'username' => $user->username ?? null,
                'email' => $user->email ?? null,
                'missingCount' => $missingCount,
                // ── new ──
                'loanBalance' => $loanBalance,
                'loansByType' => $loansByType,
                'shareCapitalBalance' => $shareCapitalBalance,
                'savingsBalance' => $savingsBalance,
                'overallBalance' => $overallBalance,
                'savedMonthlyIncome' => $savedMonthlyIncome,
                'soaData' => $soaData,
            ]
        );
    }

    public function EditProfileMember()
    {
        $userId = Auth::id();

        $user = Users_tbl::find($userId);
        $otherinfo = Otherinfo_tbl::where('user_id', $userId)->first();
        $membergovernIds = Membergovern_ids_tbl::where('user_id', $userId)->first();
        $family = Family_tbl::where('user_id', $userId)->first();
        $vehicles = Membervehi_tbl::where('user_id', $userId)->get();
        $educational = educational_tbl::where('user_id', $userId)->first();

        return view(
            'members_components.edit_profile',
            [
                'user' => $user,
                'otherinfo' => $otherinfo,
                'membergovernIds' => $membergovernIds,
                'family' => $family,
                'vehicles' => $vehicles,
                'educational' => $educational,
            ]
        );
    }

    /**
     * Downloadable Statement of Account (PDF) for the logged-in member.
     * Period = one month or one full year. Always uses Auth::id(), so a member
     * can only ever download their own statement.
     */
    public function DownloadSoa(Request $request)
    {
        $request->validate([
            'type' => 'required|in:monthly,annual',
            'month' => 'required_if:type,monthly|nullable|date_format:Y-m',
            'year' => 'required_if:type,annual|nullable|integer|min:2000|max:2100',
        ]);

        $user = Auth::user();
        $userId = $user->id;
        $otherinfo = Otherinfo_tbl::where('user_id', $userId)->first();

        if ($request->type === 'monthly') {
            $start = Carbon::createFromFormat('Y-m', $request->month)->startOfMonth();
            $end = $start->copy()->endOfMonth();
            $periodLabel = $start->format('F Y');
        } else {
            $start = Carbon::create((int) $request->year, 1, 1)->startOfDay();
            $end = $start->copy()->endOfYear();
            $periodLabel = 'Year ' . $start->year;
        }

        if ($start->isFuture()) {
            return back()->with('soa_error', 'You cannot generate a statement for a future period.');
        }

        // ── Share capital ───────────────────────────────────────────────
        $scAccount = share_capital_account_tbl::where('user_id', $userId)->first();
        $scEntries = collect();
        $scCurrent = 0.0;

        if ($scAccount) {
            $scCurrent = (float) \App\Http\Controllers\ShareCapital::paidUpForAccount($scAccount->id)['amount'];
            $creditTypes = ['Deposit', 'Subscription', ShareCapital::CONVERSION_TYPE];

            $scEntries = DB::table('share_capital_transaction_tbls')
                ->where('share_capital_account_id', $scAccount->id)
                ->whereIn(DB::raw('LOWER(status)'), ['completed', 'approved'])
                ->get()
                ->map(function ($r) use ($creditTypes) {
                    $isCredit = in_array($r->type, $creditTypes);
                    return [
                        'date' => Carbon::parse($r->transaction_date),
                        'description' => $isCredit ? 'Share Capital Contribution' : 'Share Capital Withdrawal',
                        'reference' => $r->reference_no ?? '—',
                        'amount' => $isCredit ? (float) $r->total_amount : -(float) $r->total_amount,
                    ];
                });
        }
        $sc = $this->soaBuild($scEntries, $start, $end, $scCurrent);

        // ── Savings ─────────────────────────────────────────────────────
        $svAccount = savings_account_tbl::where('user_id', $userId)->first();
        $svEntries = collect();
        $svCurrent = 0.0;

        if ($svAccount) {
            $svCurrent = (float) (new \App\Http\Controllers\SavingsController)->computeSavingsBalance($svAccount->id);

            $svEntries = DB::table('savings_transaction_tbls')
                ->where('savings_account_id', $svAccount->id)
                ->whereIn('type', ['deposit', 'withdrawal', 'interest_credit'])
                ->whereIn(DB::raw('LOWER(status)'), ['completed', 'approved', 'credited'])
                ->get()
                ->map(function ($r) {
                    $labels = ['deposit' => 'Savings Deposit', 'withdrawal' => 'Savings Withdrawal', 'interest_credit' => 'Interest Credited'];
                    return [
                        'date' => Carbon::parse($r->transaction_date),
                        'description' => $labels[$r->type] ?? ucfirst($r->type),
                        'reference' => $r->reference_no ?? '—',
                        'amount' => $r->type === 'withdrawal' ? -(float) $r->amount : (float) $r->amount,
                    ];
                });
        }
        $sv = $this->soaBuild($svEntries, $start, $end, $svCurrent);

        // ── Loan repayments made during the period ──────────────────────
        $repayments = DB::table('lending_repayments_tbls as r')
            ->join('lending_program_tbls as l', 'l.id', '=', 'r.lending_id')
            ->where('r.user_id', $userId)
            ->whereDate('r.payment_date', '>=', $start->toDateString())
            ->whereDate('r.payment_date', '<=', $end->toDateString())
            ->whereRaw("LOWER(COALESCE(r.status, 'completed')) NOT IN ('pending','voided','void')")
            ->select('r.payment_date', 'r.reference_no', 'r.payment_number', 'r.amount_paid', 'l.reference_no as loan_ref', 'l.lending_type')
            ->orderBy('r.payment_date')
            ->get();

        // ── Active loans (outstanding balance as of today) ──────────────
        $loans = DB::table('lending_program_tbls as l')
            ->leftJoin('lending_status_tbls as s', 's.lending_id', '=', 'l.id')
            ->where('l.user_id', $userId)
            ->where('l.status', 'Approved')
            ->select('l.reference_no', 'l.lending_type', 'l.lending_amount', 'l.monthly_payment', 'l.total_payment', 's.remaining_balance')
            ->get();

        AuditLog::log(
            'Downloaded SOA',
            "{$user->first_name} {$user->last_name} downloaded a Statement of Account for {$periodLabel}",
            'user',
            $userId
        );

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.member_soa', [
            'user' => $user,
            'otherinfo' => $otherinfo,
            'memberId' => 'MEM-' . str_pad($user->id, 4, '0', STR_PAD_LEFT),
            'periodType' => $request->type,
            'periodLabel' => $periodLabel,
            'start' => $start,
            'end' => $end,
            'sc' => $sc,
            'sv' => $sv,
            'repayments' => $repayments,
            'loans' => $loans,
            'generatedAt' => now('Asia/Manila'),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('SOA-' . $user->id . '-' . str_replace(' ', '-', $periodLabel) . '.pdf');
    }

    /**
     * Splits ledger entries into "this period" and "after the period", then works
     * backward from today's real balance, so opening/closing always agree with
     * the balances shown elsewhere in the system.
     */
    private function soaBuild($entries, Carbon $start, Carbon $end, float $currentBalance): array
    {
        $inPeriod = $entries->filter(fn($e) => $e['date']->between($start, $end))
            ->sortBy('date')->values();

        $afterNet = $entries->filter(fn($e) => $e['date']->gt($end))->sum('amount');
        $closing = round($currentBalance - $afterNet, 2);
        $net = $inPeriod->sum('amount');

        return [
            'rows' => $inPeriod,
            'credits' => round($inPeriod->where('amount', '>', 0)->sum('amount'), 2),
            'debits' => round(abs($inPeriod->where('amount', '<', 0)->sum('amount')), 2),
            'closing' => $closing,
            'opening' => round($closing - $net, 2),
        ];
    }

    public function UpdateProfileMember(Request $request)
    {
        $userId = Auth::id();
        $user = Users_tbl::find($userId);
        $existingInfo = Otherinfo_tbl::where('user_id', $userId)->first();
        $membergovernIds = Membergovern_ids_tbl::where('user_id', $userId)->first();
        $form = $request->_form;

        if ($form === 'vehicle') {
            $request->validate([
                'vehicle_type' => 'required|string|max:50',
                'plate_no' => 'required|string|max:50',
            ]);

            Membervehi_tbl::create([
                'user_id' => $userId,
                'vehicle_type' => $request->vehicle_type,
                'plate_no' => trim($request->plate_no),
                'quantity' => 1,
            ]);
        }

        if ($form === 'personal') {
            $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'profile_picture' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            ], [
                'profile_picture.image' => 'The profile photo must be an image.',
                'profile_picture.mimes' => 'The profile photo must be a JPG, PNG or WEBP file.',
                'profile_picture.max' => 'The profile photo must not be larger than 2MB.',
                'profile_picture.uploaded' => 'Upload failed. The file is too large for the server.',
            ]);

            $user->first_name = $request->first_name;
            $user->middle_name = $request->middle_name;
            $user->last_name = $request->last_name;
            $user->save();

            $updateData = [
                'contact_no' => $request->contact_no,
                'present_address' => $request->present_address,
                'permanent_address' => $request->permanent_address,
                'date_of_birth' => $request->date_of_birth ?: ($existingInfo->date_of_birth ?? null),
                'sex' => $request->sex,
                'civil_status' => $request->civil_status,
                'citizenship' => $request->citizenship,
                'height' => $request->height,
                'weight' => $request->weight,
                'blood_type' => $request->blood_type,
            ];

            if ($request->hasFile('profile_picture')) {
                if (!empty($existingInfo->profile_picture)) {
                    Storage::disk('public')->delete($existingInfo->profile_picture);
                }
                $updateData['profile_picture'] = $request->file('profile_picture')->store('profile_pictures', 'public');
            }

            Otherinfo_tbl::updateOrCreate(['user_id' => $userId], $updateData);
        }

        if ($form === 'employment') {
            $request->validate(['monthly_income' => 'nullable|numeric|min:0']);

            if ($request->filled('monthly_income')) {
                Otherinfo_tbl::updateOrCreate(
                    ['user_id' => $userId],
                    ['monthly_income' => $request->monthly_income]
                );
            }
        }

        if ($form === 'documents') {
            $request->validate([
                'sss_id' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
                'philhealth_id' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
                'pagibig_id' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
                'tin_id' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            ]);

            $govIdsData = [];
            foreach (['sss_id', 'philhealth_id', 'pagibig_id', 'tin_id'] as $field) {
                if ($request->hasFile($field)) {
                    $govIdsData[$field] = $request->file($field)->store('government_ids', 'public');
                }
            }

            if (!empty($govIdsData)) {
                Membergovern_ids_tbl::updateOrCreate(['user_id' => $userId], $govIdsData);
            }
        }

        if ($form === 'family') {
            $request->validate([
                'spouse_name' => 'nullable|string|max:255',
                'spouse_date_birth' => 'nullable|date',
                'spouse_place_birth' => 'nullable|string|max:255',
                'number_son' => 'nullable|integer|min:0',
                'number_daughter' => 'nullable|integer|min:0',
                'other_spec' => 'nullable|string|max:255',
            ]);

            Family_tbl::updateOrCreate(['user_id' => $userId], [
                'spouse_name' => $request->spouse_name,
                'spouse_date_birth' => $request->spouse_date_birth ?: null,
                'spouse_place_birth' => $request->spouse_place_birth,
                'number_son' => (int) $request->number_son,
                'number_daughter' => (int) $request->number_daughter,
                'other_spec' => $request->other_spec,
            ]);
        }

        AuditLog::log(
            'Updated Profile',
            "{$user->first_name} {$user->last_name} updated their profile",
            'user',
            $userId
        );

        return redirect()->route('ProfileMember')->with('success', 'Profile updated successfully!');
    }

    public function Navbar2()
    {
        $userId = Auth::id();
        $username = Auth::check() ? Auth::user()->username : null;
        $email = Auth::check() ? Auth::user()->email : null;

        $otherinfo = Otherinfo_tbl::where('user_id', $userId)->first();
        $membergovernIds = Membergovern_ids_tbl::where('user_id', $userId)->first();
        $family = Family_tbl::where('user_id', $userId)->first();

        $missingCount = 0;
        if (empty($otherinfo->contact_no)) {
            $missingCount++;
        }
        if (empty($otherinfo->present_address)) {
            $missingCount++;
        }
        if (empty($otherinfo->permanent_address)) {
            $missingCount++;
        }
        if (empty($otherinfo->date_of_birth)) {
            $missingCount++;
        }
        if (empty($otherinfo->place_of_birth)) {
            $missingCount++;
        }
        if (empty($otherinfo->sex)) {
            $missingCount++;
        }
        if (empty($otherinfo->civil_status)) {
            $missingCount++;
        }
        if (empty($otherinfo->citizenship)) {
            $missingCount++;
        }
        if (empty($otherinfo->blood_type)) {
            $missingCount++;
        }
        if (empty($otherinfo->height)) {
            $missingCount++;
        }
        if (empty($otherinfo->weight)) {
            $missingCount++;
        }
        if (empty($membergovernIds->sss_id)) {
            $missingCount++;
        }
        if (empty($membergovernIds->philhealth_id)) {
            $missingCount++;
        }
        if (empty($membergovernIds->pagibig_id)) {
            $missingCount++;
        }
        if (empty($membergovernIds->tin_id)) {
            $missingCount++;
        }

        // ★ NEW: dynamic reminder-style notifications (not stored rows — computed live)
        $navNotifications = $this->buildMemberNotifications($userId);

        return view(
            'components.navbar2',
            [
                'username' => $username,
                'email' => $email,
                'missingCount' => $missingCount,
                'navNotifications' => $navNotifications, // ★ NEW
            ]
        );
    }

    /**
     * Builds the bell-icon notification feed: loan due dates, the Share Capital
     * 2-year CBU subscription deadline, patronage refunds credited to Savings,
     * and Time Deposit maturity reminders. Computed fresh on every page load —
     * nothing here is stored in notifications_tbls.
     */
    public function buildMemberNotifications($userId)
    {
        $notifications = collect();
        $today = Carbon::today();

        // ── 0) Stored notifications (Notifications_tbl) ─────────────────
        $storedNotifications = \App\Models\Notifications_tbl::where('user_id', $userId)
            ->where('is_read', false)
            ->where('created_at', '>=', $today->copy()->subDays(30))
            ->orderByDesc('created_at')
            ->get();

        foreach ($storedNotifications as $n) {
            $notifications->push([
                'db_id' => $n->id,
                'icon' => 'fa-comment-dots',
                'color' => $n->is_important ? 'red' : 'mint',
                'title' => $n->title,
                'message' => $n->message,
                'time' => $n->created_at->diffForHumans(),
                'sort_at' => $n->created_at,
                'clickable' => !str_contains(strtolower($n->title), 'login attempt'),   // ← add
            ]);
        }

        // ── 1) Loan due dates ───────────────────────────────────────────
        $typeMap = [
            'Personal Lending' => 'Personal Loan',
            'Emergency Lending' => 'Emergency Loan',
            'Business Lending' => 'Business Loan',
            'Education Lending' => 'Education Loan',
        ];

        $loans = DB::table('lending_program_tbls')
            ->where('user_id', $userId)
            ->where('status', 'Approved')
            ->get()
            ->unique('id');

        $loanStatusByLoanIdForNotif = DB::table('lending_status_tbls')
            ->whereIn('lending_id', $loans->pluck('id'))
            ->get()
            ->keyBy('lending_id');

        foreach ($loans as $loan) {
            $statusRow = $loanStatusByLoanIdForNotif->get($loan->id);
            $effectiveDueDate = $statusRow->due_date ?? $loan->due_date;

            if (empty($effectiveDueDate)) {
                continue;
            }

            $due = Carbon::parse($effectiveDueDate);
            $daysLeft = (int) $today->diffInDays($due, false);
            $displayType = $typeMap[$loan->lending_type] ?? $loan->lending_type;

            if ($daysLeft < 0) {
                $notifications->push([
                    'icon' => 'fa-triangle-exclamation',
                    'color' => 'red',
                    'title' => 'Loan Payment Overdue',
                    'message' => "{$displayType} was due on {$due->format('M d, Y')}. Please settle to avoid additional late fees.",
                    'time' => $due->diffForHumans(),
                    'sort_at' => $due,
                    'url' => route('LoanApplication'),
                ]);
            } elseif ($daysLeft === 0) {
                $notifications->push([
                    'icon' => 'fa-calendar-day',
                    'color' => 'red',
                    'title' => 'Loan Payment Due Today',
                    'message' => "{$displayType} of ₱" . number_format((float) ($loan->monthly_payment ?? 0), 2) . " is due today ({$due->format('M d, Y')}).",
                    'time' => $due->diffForHumans(),
                    'sort_at' => $due,
                    'url' => route('LoanApplication'),
                ]);
            } elseif ($daysLeft <= 7) {
                $notifications->push([
                    'icon' => 'fa-calendar-days',
                    'color' => 'gold',
                    'title' => 'Loan Payment Due This Week',
                    'message' => "{$displayType} is due on {$due->format('M d, Y')} (in {$daysLeft} day" . ($daysLeft === 1 ? '' : 's') . ').',
                    'time' => $due->diffForHumans(),
                    'sort_at' => $due,
                    'url' => route('LoanApplication'),
                ]);
            }
        }

        // ── Resignation Request status ──────────────────────────────────
        $resignationRequest = \App\Models\ResignationRequest_tbl::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->first();

        if ($resignationRequest) {
            if ($resignationRequest->status === 'pending') {
                $notifications->push([
                    'icon' => 'fa-file-signature',
                    'color' => 'gold',
                    'title' => 'Resignation Request Submitted',
                    'message' => 'Your resignation request has been submitted and is awaiting admin review.',
                    'time' => $resignationRequest->created_at->diffForHumans(),
                    'sort_at' => $resignationRequest->created_at,
                    'url' => route('ProfileMember'),
                ]);
            } elseif ($resignationRequest->status === 'approved' && $resignationRequest->updated_at >= $today->copy()->subDays(30)) {
                $notifications->push([
                    'icon' => 'fa-circle-check',
                    'color' => 'mint',
                    'title' => 'Resignation Request Approved',
                    'message' => $resignationRequest->withdraw_share_capital
                        ? 'Your resignation has been approved. Your share capital will be released after the 60-day holding period.'
                        : 'Your resignation has been approved. Your share capital will remain with the cooperative.',
                    'time' => $resignationRequest->updated_at->diffForHumans(),
                    'sort_at' => $resignationRequest->updated_at,
                    'url' => route('ProfileMember'),
                ]);
            } elseif ($resignationRequest->status === 'rejected' && $resignationRequest->updated_at >= $today->copy()->subDays(30)) {
                $notifications->push([
                    'icon' => 'fa-triangle-exclamation',
                    'color' => 'red',
                    'title' => 'Resignation Request Rejected',
                    'message' => 'Your resignation request was not approved. Reason: ' . ($resignationRequest->rejection_reason ?? 'No reason provided.'),
                    'time' => $resignationRequest->updated_at->diffForHumans(),
                    'sort_at' => $resignationRequest->updated_at,
                    'url' => route('ProfileMember'),
                ]);
            }
        }

        // ── 1b) Loan application decisions, disbursement, repayments ────
        $allLoansForNotif = DB::table('lending_program_tbls')
            ->where('user_id', $userId)
            ->get();

        foreach ($allLoansForNotif as $loan) {
            $displayType = $typeMap[$loan->lending_type] ?? $loan->lending_type;
            $ref = $loan->reference_no ?? '—';
            $statusLower = strtolower($loan->status ?? '');
            $decidedAt = Carbon::parse($loan->updated_at ?? $loan->created_at);

            // Approved
            if ($statusLower === 'approved' && $decidedAt >= $today->copy()->subDays(30)) {
                $notifications->push([
                    'icon' => 'fa-circle-check',
                    'color' => 'mint',
                    'title' => 'Loan Application Approved',
                    'message' => "Your {$displayType} application of ₱" . number_format((float) $loan->lending_amount, 2)
                        . " has been approved (Ref: {$ref}).",
                    'time' => $decidedAt->diffForHumans(),
                    'sort_at' => $decidedAt,
                    'url' => route('LoanApplication'),
                ]);
            }

            // Declined / rejected
            if (in_array($statusLower, ['declined', 'rejected'], true) && $decidedAt >= $today->copy()->subDays(30)) {
                $notifications->push([
                    'icon' => 'fa-circle-xmark',
                    'color' => 'red',
                    'title' => 'Loan Application Declined',
                    'message' => "Your {$displayType} application (Ref: {$ref}) was declined."
                        . (!empty($loan->decline_reason) ? " Reason: {$loan->decline_reason}" : ''),
                    'time' => $decidedAt->diffForHumans(),
                    'sort_at' => $decidedAt,
                    'url' => route('LoanApplication'),
                ]);
            }

            // Disbursed (money released)
            if ($statusLower === 'approved' && !empty($loan->disbursed_at)) {
                $disbursedAt = Carbon::parse($loan->disbursed_at);

                if ($disbursedAt >= $today->copy()->subDays(30)) {
                    $notifications->push([
                        'icon' => 'fa-file-invoice-dollar',
                        'color' => 'mint',
                        'title' => 'Loan Released',
                        'message' => "₱" . number_format((float) ($loan->net_proceeds ?? $loan->lending_amount), 2)
                            . " from your {$displayType} has been released"
                            . (!empty($loan->disbursement_method) ? " via {$loan->disbursement_method}" : '')
                            . " (Ref: " . ($loan->disbursement_reference ?? $ref) . ").",
                        'time' => $disbursedAt->diffForHumans(),
                        'sort_at' => $disbursedAt,
                        'url' => route('LoanApplication'),
                    ]);
                }
            }
        }

        // Loan repayments recorded in the last 14 days
        $recentRepayments = DB::table('lending_repayments_tbls')
            ->where('user_id', $userId)
            ->where('created_at', '>=', $today->copy()->subDays(14))
            ->get();

        foreach ($recentRepayments as $rp) {
            $paidAt = Carbon::parse($rp->created_at);

            $notifications->push([
                'icon' => 'fa-hand-holding-dollar',
                'color' => 'mint',
                'title' => 'Loan Payment Received',
                'message' => "Your payment of ₱" . number_format((float) $rp->amount_paid, 2)
                    . " (installment {$rp->payment_number}) was recorded (Ref: " . ($rp->reference_no ?? '—') . ").",
                'time' => $paidAt->diffForHumans(),
                'sort_at' => $paidAt,
                'url' => route('LoanApplication'),
            ]);
        }

        // ── 2) Share Capital — 2-year CBU subscription deadline ─────────
        $scAccount = DB::table('share_capital_account_tbls')->where('user_id', $userId)->first();

        if ($scAccount) {
            $paidUp = DB::table('share_capital_transaction_tbls')
                ->where('share_capital_account_id', $scAccount->id)
                ->whereIn('type', ['Deposit', 'Subscription', ShareCapital::CONVERSION_TYPE])
                ->whereIn('status', ['Completed', 'completed'])
                ->sum('total_amount') ?? 0;

            $targetAmount = 10000;
            $deadline = Carbon::parse($scAccount->created_at)->addYears(2);
            $daysLeft = (int) $today->diffInDays($deadline, false);

            if ($paidUp < $targetAmount && $daysLeft <= 90) {
                $remaining = $targetAmount - $paidUp;

                $notifications->push([
                    'icon' => 'fa-layer-group',
                    'color' => $daysLeft < 0 ? 'red' : 'gold',
                    'title' => $daysLeft < 0 ? 'Share Capital Subscription Overdue' : 'Share Capital Deadline Approaching',
                    'message' => $daysLeft < 0
                        ? 'Your 2-year window to complete your ₱' . number_format($targetAmount, 2) . " share capital subscription ended on {$deadline->format('M d, Y')}. ₱" . number_format($remaining, 2) . ' remains unpaid.'
                        : 'You have ₱' . number_format($remaining, 2) . ' remaining to complete your ₱' . number_format($targetAmount, 2) . " share capital subscription by {$deadline->format('M d, Y')} ({$daysLeft} day" . ($daysLeft === 1 ? '' : 's') . ' left).',
                    'time' => $deadline->diffForHumans(),
                    'sort_at' => $deadline,
                ]);
            }
        }

        // ── 2b) Share Capital — recently approved/completed transactions
        if ($scAccount) {
            $scRecentTxs = DB::table('share_capital_transaction_tbls')
                ->where('share_capital_account_id', $scAccount->id)
                ->whereIn('type', ['Deposit', 'Subscription', 'Withdrawal', ShareCapital::CONVERSION_TYPE])
                ->whereRaw('LOWER(status) IN (?, ?)', ['completed', 'approved'])
                ->where('updated_at', '>=', $today->copy()->subDays(14))
                ->get();

            foreach ($scRecentTxs as $tx) {
                $isDeposit = in_array($tx->type, ['Deposit', 'Subscription', ShareCapital::CONVERSION_TYPE]);

                $notifications->push([
                    'icon' => 'fa-circle-check',
                    'color' => 'mint',
                    'title' => $isDeposit ? 'Share Capital Deposit Approved' : 'Share Capital Withdrawal Approved',
                    'message' => 'Your ' . number_format((float) $tx->shares, 2) . ' share(s) (₱'
                        . number_format((float) $tx->total_amount, 2) . ') '
                        . ($isDeposit ? 'deposit has been completed' : 'withdrawal has been approved')
                        . ' (Ref: ' . $tx->reference_no . ').',
                    'time' => Carbon::parse($tx->updated_at)->diffForHumans(),
                    'sort_at' => Carbon::parse($tx->updated_at),
                    'url' => route('Financial', ['tab' => 'share_capital']),
                ]);
            }
        }

        $savingsAccount = savings_account_tbl::where('user_id', $userId)->first();

        if ($savingsAccount) {
            // ── 3b) Savings — recently approved/completed transactions ──
            $svRecentTxs = savings_transaction_tbl::where('savings_account_id', $savingsAccount->id)
                ->whereIn('type', ['deposit', 'withdrawal'])
                ->whereRaw('LOWER(status) IN (?, ?)', ['completed', 'approved'])
                ->where('updated_at', '>=', $today->copy()->subDays(14))
                ->get();

            foreach ($svRecentTxs as $tx) {
                $isDeposit = $tx->type === 'deposit';

                $notifications->push([
                    'icon' => 'fa-circle-check',
                    'color' => 'mint',
                    'title' => $isDeposit ? 'Savings Deposit Approved' : 'Savings Withdrawal Approved',
                    'message' => 'Your ₱' . number_format((float) $tx->amount, 2) . ' '
                        . ($isDeposit ? 'deposit has been completed' : 'withdrawal has been approved')
                        . ' (Ref: ' . $tx->reference_no . ').',
                    'time' => Carbon::parse($tx->updated_at)->diffForHumans(),
                    'sort_at' => Carbon::parse($tx->updated_at),
                    'url' => route('Financial', ['tab' => 'savings']),
                ]);
            }

            // ── 3) Patronage refund credited to Savings (last 30 days) ──
            $patronageTxs = savings_transaction_tbl::where('savings_account_id', $savingsAccount->id)
                ->where('type', 'deposit')
                ->where('reference_no', 'like', 'PAT-%')
                ->where('transaction_date', '>=', $today->copy()->subDays(30))
                ->get();

            foreach ($patronageTxs as $tx) {
                $notifications->push([
                    'icon' => 'fa-piggy-bank',
                    'color' => 'mint',
                    'title' => 'Patronage Refund Credited',
                    'message' => '₱' . number_format($tx->amount, 2) . " patronage refund has been credited to your Savings account (Ref: {$tx->reference_no}).",
                    'time' => Carbon::parse($tx->created_at ?? $tx->transaction_date)->diffForHumans(),
                    'sort_at' => Carbon::parse($tx->created_at ?? $tx->transaction_date),
                    'url' => route('Financial', ['tab' => 'savings']),
                ]);
            }
        }

        // ── 5) Seminars — upcoming sessions ─────────────────────────────
        $seminarTypeLabelsNotif = [
            'pmes' => 'PMES',
            'fundamentals' => 'Cooperative Fundamentals',
            'finance' => 'Cooperative Finance',
        ];

        $seminarAttendeeRecordsNotif = \App\Models\SeminarAttendees_tbl::with('seminar')
            ->where('user_id', $userId)
            ->get()
            ->filter(fn($a) => $a->seminar);

        $upcomingSeminarNotifs = $seminarAttendeeRecordsNotif
            ->filter(fn($a) => $a->status === 'pending'
                && $a->seminar->schedule_datetime >= $today);

        foreach ($upcomingSeminarNotifs as $a) {
            $s = $a->seminar;
            $sessionDate = Carbon::parse($s->schedule_datetime);
            $daysLeft = (int) $today->diffInDays($sessionDate, false);
            $displayLabel = $seminarTypeLabelsNotif[$s->seminar_type] ?? ucfirst($s->seminar_type);
            $deliveryText = $s->delivery_type === 'online'
                ? 'Online'
                : 'F2F · ' . ($s->meetup_place ?? 'Venue TBA');

            $notifications->push([
                'icon' => 'fa-graduation-cap',
                'color' => 'gold',
                'title' => 'Seminar Coming Up',
                'message' => "{$displayLabel} is scheduled on {$sessionDate->format('M d, Y')}"
                    . ($daysLeft === 0 ? ' (today)' : ($daysLeft === 1 ? ' (tomorrow)' : " (in {$daysLeft} days)"))
                    . " · {$deliveryText}.",
                'time' => $sessionDate->diffForHumans(),
                'sort_at' => $sessionDate,
                'clickable' => false,   // ← replaces 'url' => route('Seminars')
            ]);
        }

        // ── 6) Seminars — remaining types with no session scheduled yet ─
        $scheduledSeminarTypes = $upcomingSeminarNotifs->pluck('seminar.seminar_type');

        $seminarCompletionNotif = \App\Models\SeminarCompletions_tbl::where('user_id', $userId)->first();
        $seminarCompletedFlagsNotif = [
            'pmes' => (bool) ($seminarCompletionNotif->pmes_completed ?? false),
            'fundamentals' => (bool) ($seminarCompletionNotif->fundamentals_completed ?? false),
            'finance' => (bool) ($seminarCompletionNotif->finance_completed ?? false),
        ];

        foreach ($seminarCompletedFlagsNotif as $type => $done) {
            if ($done || $scheduledSeminarTypes->contains($type)) {
                continue;
            }

            $displayLabel = $seminarTypeLabelsNotif[$type] ?? ucfirst($type);

            $notifications->push([
                'icon' => 'fa-hourglass-half',
                'color' => 'gold',
                'title' => 'Seminar Not Yet Scheduled',
                'message' => "{$displayLabel} is still required to complete your membership training. No session has been scheduled yet — check back soon.",
                'time' => 'Pending',
                'sort_at' => $today->copy()->addDays(90),
                'clickable' => false,   // ← replaces 'url' => route('Seminars')
            ]);
        }

        // ── Read state: DB table (survives logout) + session fallback ───
        $readIds = [];
        try {
            $readIds = DB::table('notification_reads_tbls')
                ->where('user_id', $userId)
                ->pluck('notification_key')
                ->flip()
                ->all();
        } catch (\Throwable $e) {
            // Table not created yet — fall back to session only.
        }
        $readIds = $readIds + session('read_notification_ids', []);

        $nowTs = Carbon::now()->getTimestamp();

        return $notifications
            ->unique(fn($n) => $n['title'] . '|' . $n['message'])
            ->map(function ($n) {
                $n['id'] = $n['db_id'] ?? md5($n['title'] . '|' . $n['message']);
                return $n;
            })
            ->reject(fn($n) => isset($readIds[$n['id']]))
            // Newest first. Future dates (due dates, upcoming seminars) are
            // capped at "now" so they don't sit above something that just happened.
            ->sortByDesc(fn($n) => min($n['sort_at']->getTimestamp(), $nowTs))
            ->values();
    }

    public function MarkNotificationRead(Request $request)
    {
        $request->validate(['id' => 'required|string|max:64']);

        // If this id belongs to a real stored notification, mark it read in the DB.
        \App\Models\Notifications_tbl::where('user_id', Auth::id())
            ->where('id', $request->id)
            ->update(['is_read' => true]);

        DB::table('notification_reads_tbls')->insertOrIgnore([
            'user_id' => Auth::id(),
            'notification_key' => $request->id,
            'read_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Also record it for the computed (non-DB) notifications, which use a
        // hashed id and have no row to update.
        $read = session('read_notification_ids', []);
        $read[$request->id] = true;
        session(['read_notification_ids' => $read]);

        return response()->json(['success' => true]);
    }

    /**
     * Marks every notification currently shown in the bell as read:
     * stored ones (Notifications_tbl) AND the computed reminders.
     */
    public function MarkAllNavNotificationsRead(Request $request)
    {
        $userId = Auth::id();

        // Everything the bell is showing right now (already excludes read ones)
        $keys = $this->buildMemberNotifications($userId)->pluck('id')->unique()->values();

        // Stored notifications
        \App\Models\Notifications_tbl::where('user_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true, 'updated_at' => now()]);

        // Computed reminders (hashed ids) + stored ids, persisted so they survive logout
        if ($keys->isNotEmpty()) {
            $now = now();
            DB::table('notification_reads_tbls')->insertOrIgnore(
                $keys->map(fn($k) => [
                    'user_id' => $userId,
                    'notification_key' => (string) $k,
                    'read_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all()
            );
        }

        // Session fallback, same as the single-notification version
        $read = session('read_notification_ids', []);
        foreach ($keys as $k) {
            $read[$k] = true;
        }
        session(['read_notification_ids' => $read]);

        return response()->json(['success' => true, 'count' => $keys->count()]);
    }

    public function logout()
    {
        if (Auth::check()) {
            \App\Services\SingleSession::release(Auth::id());
        }

        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function UserHandle()
    {
        $user = Auth::user();

        // Member-based accounts (regular members AND Allied Workers promoted on
        // the same account, i.e. base_role=member) go to MemberPortal. Only real
        // admin/staff accounts (base_role=null) go to the dashboard.
        if (method_exists($user, 'isMemberBased') && $user->isMemberBased()) {
            if (strtolower((string) $user->role) === 'pending') {
                auth()->logout();
                request()->session()->invalidate();
                request()->session()->regenerateToken();

                return redirect()->route('login')
                    ->withErrors(['login' => 'Your account is still pending approval']);
            }

            if (strtolower((string) $user->status) === 'reactivation_pending') {
                auth()->logout();
                request()->session()->invalidate();
                request()->session()->regenerateToken();

                return redirect()->route('login')
                    ->withErrors(['login' => 'Your reactivation request is still under review.']);
            }

            if ($this->isLockedOutStatus($user)) {
                return redirect()->route('member.inactive');
            }

            return redirect()->route('MemberPortal')
                ->with('message', 'Login successfully!')
                ->with('just_logged_in', true);
        }

        return redirect()->route('dashboard')->with('message', 'Login successfully!');
    }

    /**
     * Dedicated page for inactive member accounts. Strictly read-only: it
     * displays the member's account details and any share capital they still
     * hold (paidUpForAccount), and offers a single Request Reactivation
     * action. No financial record is ever created, changed or deleted here.
     */
    public function InactivePage()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!method_exists($user, 'isMemberBased') || !$user->isMemberBased()) {
            return redirect()->route('dashboard');
        }

        $isInactive = $this->isLockedOutStatus($user)
            || strtolower((string) $user->status) === 'reactivation_pending';

        if (!$isInactive) {
            return redirect()->route('MemberPortal');
        }

        $otherInfo = $user->otherinfo;

        $scAccount = share_capital_account_tbl::where('user_id', $user->id)->first();
        $scBalance = ['shares' => 0.0, 'amount' => 0.0];
        if ($scAccount) {
            $scBalance = \App\Http\Controllers\ShareCapital::paidUpForAccount($scAccount->id);
        }

        return view('members_components.inactive', compact('user', 'otherInfo', 'scAccount', 'scBalance'));
    }

    /**
     * Submits a reactivation request for the authenticated member.
     *
     * Backend-enforced single request: only an inactive account may move to
     * reactivation_pending. A member whose request is already under review is
     * rejected here regardless of what the UI shows. Always operates on
     * Auth::id() — no member ID is accepted from the client. Only the status
     * column changes; share capital, savings, loans and all financial records
     * are untouched.
     */
    public function ReactivateAccount(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $status = strtolower((string) $user->status);

        if ($status === 'reactivation_pending') {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['login' => 'Your reactivation request is still under review.']);
        }

        $isInactive = $this->isLockedOutStatus($user);

        if (!$isInactive) {
            return redirect()->route('MemberPortal');
        }

        $user->status = 'reactivation_pending';
        $user->save();

        AuditLog::log(
            'Reactivation Requested',
            "Reactivation request submitted for {$user->first_name} {$user->last_name} (ID: {$user->id}).",
            'member',
            $user->id
        );

        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->withErrors(['login' => 'Your reactivation request is now under review.']);
    }

    public function login(Request $request)
    {
        $incomingFields = $request->validate([
            'login' => 'required',
            'password' => 'required',
        ]);

        $loginInput = $incomingFields['login'];

        // Check if user exists by email
        // Check if user exists by email
        $user = DB::table('users_tbls')->where('email', $loginInput)->first();

        if (!$user) {
            return redirect()->back()
                ->withErrors(['login' => 'Incorrect email or password. Please try again.'])
                ->withInput($request->only('login'));
        }

        // Attempt authentication
        $credentials = [
            'email' => $user->email,
            'password' => $incomingFields['password'],
        ];

        if (auth()->validate($credentials)) {
            $user = Users_tbl::find($user->id);

            // after
            if (\App\Services\SingleSession::activeElsewhere($user->id)) {
                \App\Services\SingleSession::createSwitchRequest($user, $request);

                return redirect()->back()
                    ->withInput($request->only('login'));
            }

            // Member-based accounts (regular members AND Allied Workers promoted
            // on the same account — base_role=member) always flow through the
            // membership checks and land on the Member portal. Only real
            // admin/staff accounts (base_role=null) bypass membership checks.
            if (!(method_exists($user, 'isMemberBased') && $user->isMemberBased())) {
                if ($user->status === 'inactive') {
                    auth()->logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    return redirect()->back()
                        ->withErrors(['login' => 'Your account has been deactivated. Contact the main administrator.'])
                        ->withInput($request->only('login'));
                }
                if ($this->beginTwoFactorChallenge($request, $user)) {
                    return redirect()->route('2fa.challenge');
                }

                auth()->login($user);
                $request->session()->regenerate();
                $request->session()->flash('just_logged_in', true);

                return redirect()->route('UserHandle');
            }

            if (strtolower((string) $user->role) === 'pending') {
                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->back()
                    ->withErrors(['login' => 'Your account is still pending approval'])
                    ->withInput($request->only('login'));
            }

            $otherInfo = DB::table('otherinfo_tbls')
                ->where('user_id', $user->id)
                ->first();

            if (!$otherInfo || $otherInfo->approval_status === 'Pending') {

                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->back()
                    ->withErrors(['login' => 'Your account is still pending approval'])
                    ->withInput($request->only('login'));

            } elseif (!$otherInfo || $otherInfo->approval_status === 'Declined') {
                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->back()
                    ->withErrors(['login' => 'Your membership application is declined.'])
                    ->withInput($request->only('login'));
            } else {

                if (strtolower((string) $user->status) === 'reactivation_pending') {
                    auth()->logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    return redirect()->back()
                        ->withErrors(['login' => 'Your reactivation request is still under review.'])
                        ->withInput($request->only('login'));
                }

                if ($this->isLockedOutStatus($user)) {
                    auth()->login($user);
                    $request->session()->regenerate();
                    $request->session()->flash('just_logged_in', true);

                    return redirect()->route('member.inactive');
                }

                if ($this->beginTwoFactorChallenge($request, $user)) {
                    return redirect()->route('2fa.challenge');
                }

                auth()->login($user);
                $request->session()->regenerate();
                $request->session()->flash('just_logged_in', true);

                return redirect()->route('UserHandle');

            }

        } else {
            return redirect()->back()
                ->withErrors(['login' => 'Incorrect email or password. Please try again.'])
                ->withInput($request->only('login'));
        }

        // if (auth()->attempt($credentials)) {
        //     $otherInfo = DB::table('otherinfo_tbls')
        //         ->where('user_id', auth()->id())
        //         ->first();

        //     if (!$otherInfo || $otherInfo->status === 'Pending') {
        //         auth()->logout();
        //         $request->session()->invalidate();
        //         $request->session()->regenerateToken();

        //         return redirect()->back()
        //             ->withErrors(['login' => 'Your account is still pending approval.'])
        //             ->withInput($request->only('login'));
        //     } else if (!$otherInfo || $otherInfo->status === 'Declined') {
        //         auth()->logout();
        //         $request->session()->invalidate();
        //         $request->session()->regenerateToken();

        //         return redirect()->back()
        //             ->withErrors(['login' => 'Your membership application is declined.'])
        //             ->withInput($request->only('login'));
        //     } else {

        //         $request->session()->regenerate();
        //         return redirect()->route('UserHandle');

        //     }

        // } else {
        //     return redirect()->back()
        //         ->withErrors(['login' => 'Incorrect password. Please try again.'])
        //         ->withInput($request->only('login'));
        // }
    }

    /**
     * If the account is a privileged user (General Manager / Main Admin) with
     * enabled 2FA, authenticate it, rotate the session id, and drop the user
     * into the pending-2FA challenge state (session holds only the user id).
     * The final verified session is only granted after the challenge passes.
     *
     * @return bool Whether a 2FA challenge was started (caller must redirect
     *              the user to the challenge page when true).
     */
    private function beginTwoFactorChallenge(Request $request, Users_tbl $user): bool
    {
        if (!method_exists($user, 'requiresTwoFactor') || !method_exists($user, 'twoFactorEnabled')) {
            return false;
        }

        if (!$user->requiresTwoFactor() || !$user->twoFactorEnabled()) {
            return false;
        }

        auth()->login($user);

        // Regenerate the session id before entering the pending state so the
        // pre-verification session cannot be fixed to a known id.
        $request->session()->regenerate();
        $request->session()->forget('2fa.verified');
        $request->session()->put('2fa.pending_user_id', $user->id);

        AuditLog::log(
            '2FA Challenge Initiated',
            "Two-factor authentication challenge started for user #{$user->id}.",
            'user',
            $user->id
        );

        return true;
    }

    public function checkEmail(Request $request)
    {
        $exists = \App\Models\Users_tbl::where('email', $request->email)->exists();

        return response()->json(['exists' => $exists]);
    }

    public function registration(Request $request)
    {
        try {

            $request->validate([
                'first_name' => 'required',
                'middle_name' => 'nullable|string|max:255',
                'last_name' => 'required',
                'profile_picture' => 'nullable|image|max:2048',
                'date_of_birth' => 'required|date',
                'place_of_birth' => 'required',
                'email' => ['required', 'email', Rule::unique('users_tbls', 'email')],
                'password' => 'required|string|min:8|confirmed',
                'membership_category' => 'required',
                'civil_status' => 'required',
                'number_son' => 'nullable|integer',
                'number_daughter' => 'nullable|integer',
                'other_spec' => 'nullable',
                'contact_no' => 'nullable|string|max:255',

                'sss_id' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
                'philhealth_id' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
                'pagibig_id' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
                'tin_id' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',

                'uv_plate_no' => 'nullable|array',
                'uv_plate_no.*' => 'nullable|string',
                'taxi_plate_no' => 'nullable|array',
                'taxi_plate_no.*' => 'nullable|string',
                'bus_plate_no' => 'nullable|array',
                'bus_plate_no.*' => 'nullable|string',
                'mini_bus_plate_no' => 'nullable|array',
                'mini_bus_plate_no.*' => 'nullable|string',
                'jeep_plate_no' => 'nullable|array',
                'jeep_plate_no.*' => 'nullable|string',
                'multi_cab_plate_no' => 'nullable|array',
                'multi_cab_plate_no.*' => 'nullable|string',
                'tricycle_plate_no' => 'nullable|array',
                'tricycle_plate_no.*' => 'nullable|string',

                'total_uv' => 'nullable|integer|min:0',
                'total_taxi' => 'nullable|integer|min:0',
                'total_bus' => 'nullable|integer|min:0',
                'total_mini_bus' => 'nullable|integer|min:0',
                'total_jeep' => 'nullable|integer|min:0',
                'total_multi_cab' => 'nullable|integer|min:0',
                'total_tricycle' => 'nullable|integer|min:0',

                'signature' => 'required',
            ]);

            // Profile picture
            $profilePicturePath = null;
            if ($request->hasFile('profile_picture')) {
                $profilePicturePath = $request->file('profile_picture')->store('profile_pictures', 'public');
            }

            // Create user
            $users = Users_tbl::create([
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name,
                'username' => $request->username,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'role' => 'Pending',
            ]);

            // Spouse
            Family_tbl::create([
                'user_id' => $users->id,
                'spouse_name' => $request->spouse_name,
                'spouse_date_birth' => $request->spouse_date_birth ?: null,
                'spouse_place_birth' => $request->spouse_place_birth,
                'number_son' => $request->number_son,
                'number_daughter' => $request->number_daughter,
                'other_spec' => $request->other_spec,
            ]);

            $governmentIds = ['user_id' => $users->id];
            $fileFields = ['sss_id', 'philhealth_id', 'pagibig_id', 'tin_id'];

            foreach ($fileFields as $field) {
                if ($request->hasFile($field)) {
                    $governmentIds[$field] = $request->file($field)->store('government_ids', 'public');
                } else {
                    $governmentIds[$field] = null;
                }
            }

            Membergovern_ids_tbl::create($governmentIds);

            // In your RegisterController or wherever you save the member
            $emailVerified = Session::get('email_otp_verified_email') === $request->email ? 1 : 0;

            // Other info
            Otherinfo_tbl::create([
                'user_id' => $users->id,
                'membership_category' => $request->membership_category,
                'email_verified' => $emailVerified,
                'date_of_birth' => $request->date_of_birth,
                'place_of_birth' => $request->place_of_birth,
                'sex' => $request->sex,
                'civil_status' => $request->civil_status,
                'citizenship' => $request->citizenship, // ← add this
                'skills' =>
                    $request->skills_expertise,  // ← note: form uses skills_expertise
                'signature' => $request->signature,
                'profile_picture' => $profilePicturePath,
                'approval_status' => 'Pending',
                'membership_status' => 'Unofficial',
                'contact_no' => $request->contact_no,
            ]);

            // Vehicles
            $vehicleTypes = [
                'UV' => ['plate_name' => 'uv_plate_no', 'qty_name' => 'total_uv'],
                'TAXI' => ['plate_name' => 'taxi_plate_no', 'qty_name' => 'total_taxi'],
                'BUS' => ['plate_name' => 'bus_plate_no', 'qty_name' => 'total_bus'],
                'MINI BUS' => ['plate_name' => 'mini_bus_plate_no', 'qty_name' => 'total_mini_bus'],
                'JEEP' => ['plate_name' => 'jeep_plate_no', 'qty_name' => 'total_jeep'],
                'MULTI-CAB' => ['plate_name' => 'multi_cab_plate_no', 'qty_name' => 'total_multi_cab'],
                'TRICYCLE' => ['plate_name' => 'tricycle_plate_no', 'qty_name' => 'total_tricycle'],
            ];

            foreach ($vehicleTypes as $type => $fields) {
                $quantity = (int) $request->input($fields['qty_name'], 0);
                $plates = $request->input($fields['plate_name']);

                if ($quantity <= 0 || empty($plates) || !is_array($plates)) {
                    continue;
                }

                foreach ($plates as $plate_no) {
                    $plate_no = trim((string) ($plate_no ?? ''));
                    if ($plate_no === '') {
                        continue;
                    }

                    Membervehi_tbl::create([
                        'user_id' => $users->id,
                        'plate_no' => $plate_no,
                        'vehicle_type' => $type,
                        'quantity' => 1,
                    ]);
                }
            }

            AuditLog::log(
                'User Registered',
                "New user registered: {$request->first_name} {$request->last_name} ({$request->email})",
                'user',
                $users->id
            );

            return redirect()->route('RegisterPage')->with('success', 'Create account successfully!');

        } catch (\Exception $e) {
            throw $e;
        }

    }
}
