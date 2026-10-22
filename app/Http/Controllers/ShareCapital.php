<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ResignationRequest_tbl;
use App\Models\Users_tbl;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ShareCapital extends Controller
{
    // ─────────────────────────────────────────────────────────────────────
    // COOPERATIVE POLICY CONSTANTS (not stored per-row — fixed by bylaws)
    // ─────────────────────────────────────────────────────────────────────
    const PAR_VALUE = 200;     // ₱ per share

    const TARGET_SHARES = 50;      // shares required for full subscription

    const TARGET_AMOUNT = 10000;   // = TARGET_SHARES * PAR_VALUE

    const INSTALLMENT_SLOTS = 8;       // 8 quarters = 2 years

    const ISC_SPLIT = 0.60;    // 60% Interest on Share Capital

    const PATRONAGE_SPLIT = 0.40;    // 40% Patronage Refund

    const CONVERSION_TYPE = 'Savings to Share Capital Conversion'; // ledger type for savings → share capital conversions

    const ELIGIBLE_SHARES = 10;  // paid-up shares required for an account to be eligible/active (member-side rule)

    /**
     * Show the Share Capital form (first-time subscription only).
     */
    public function index()
    {
        $memberId = Auth::id();

        $account = DB::table('share_capital_account_tbls')
            ->where('user_id', $memberId)
            ->first();

        if ($account) {
            [$currentBalance, $currentShares] = $this->computeBalanceAndShares($account);

            if ($currentShares >= 10 && !session('success')) {
                return redirect()->route('ShareCapitalMember');
            }
        } else {
            $currentBalance = 0;
            $currentShares = 0;
        }

        $dividendRateRecord = $this->getDividendRateRecord();
        $dividendRate = $dividendRateRecord->rate ?? 8.5;
        $dividendRateYear = $dividendRateRecord->effective_year ?? now()->year;
        $rateHistory = $this->getRateHistory();
        $dividendHistory = $account ? $this->getDividendHistory($account->id) : collect();

        $contributions = DB::table('share_capital_transaction_tbls')
            ->where('share_capital_account_id', $account->id ?? 0)
            ->where('status', '!=', 'failed')
            ->orderByDesc('transaction_date')
            ->get();

        $dividendDates = $this->computeDividendDates();
        extract($dividendDates);

        $projectedNextDividend = round($currentBalance * ($dividendRate / 100) / 2, 2);
        $totalDividendsEarned = $dividendHistory->where('status', 'Paid')->sum('dividend_amount');

        $lastDividend = $dividendHistory->where('status', 'Paid')->sortByDesc(fn($d) => $d->date_paid)->first();
        $lastDividendAmount = $lastDividend->dividend_amount ?? null;
        $lastDividendDate = $lastDividend ? Carbon::parse($lastDividend->date_paid)->format('M d, Y') : null;
        $lastDividendPeriod = $lastDividend->period_label ?? null;

        return view('ShareCapitalForm.share_capital_form', compact(
            'currentBalance',
            'currentShares',
            'contributions',
            'dividendRate',
            'dividendRateYear',
            'rateHistory',
            'dividendHistory',
            'lastDividend',
            'lastDividendAmount',
            'lastDividendDate',
            'lastDividendPeriod',
            'nextDividendDate',
            'nextDividendPeriod',
            'projectedNextDividend',
            'totalDividendsEarned',
            'prevDividendDate',
            'prevDividendPeriod',
            'futureDate2',
            'futurePeriod2',
        ));
    }

    /**
     * Show the member Share Capital page (Deposit/Withdrawal).
     */
    public function memberIndex()
    {
        $username = Auth::check() ? Auth::user()->username : null;
        $email = Auth::check() ? Auth::user()->email : null;
        $memberId = Auth::id();

        $account = DB::table('share_capital_account_tbls')
            ->where('user_id', $memberId)
            ->first();

        if ($account) {
            [$currentBalance, $currentShares] = $this->computeBalanceAndShares($account);
        } else {
            $currentBalance = 0;
            $currentShares = 0;
        }

        $contributions = DB::table('share_capital_transaction_tbls')
            ->where('share_capital_account_id', $account->id ?? 0)
            ->where('status', '!=', 'failed')
            ->orderByDesc('transaction_date')
            ->get();

        // ── Target / paid-up progress ─────────────────────────────────
        $targetAmount = self::TARGET_AMOUNT;
        $targetShares = self::TARGET_SHARES;
        $parValue = self::PAR_VALUE;
        $paidUpPercent = $targetAmount > 0 ? min(100, round(($currentBalance / $targetAmount) * 100)) : 0;
        $remainingToTarget = max(0, $targetAmount - $currentBalance);
        $certificateEligible = $currentBalance >= $targetAmount;

        // ── Installment (Capital Build-Up) timeline ─────────────────────
        $timelineData = $this->buildInstallmentTimeline($account, $contributions);
        $installmentTimeline = $timelineData['slots'];
        $advancePayment = $timelineData['advance'];
        $nextDueSlot = collect($installmentTimeline)->firstWhere('status', 'due')
            ?? collect($installmentTimeline)->firstWhere('status', 'upcoming');

        // ── Dividend data ────────────────────────────────────────────
        $dividendRateRecord = $this->getDividendRateRecord();
        $dividendRate = $dividendRateRecord->rate ?? 8.5;
        $dividendRateYear = $dividendRateRecord->effective_year ?? now()->year;
        $rateHistory = $this->getRateHistory();
        $dividendHistory = $account ? $this->getDividendHistory($account->id) : collect();

        $lastDividend = $dividendHistory->where('status', 'Paid')->sortByDesc(fn($d) => $d->date_paid)->first();
        $lastDividendAmount = $lastDividend->dividend_amount ?? null;
        $lastDividendDate = $lastDividend ? Carbon::parse($lastDividend->date_paid)->format('M d, Y') : null;
        $lastDividendPeriod = $lastDividend->period_label ?? null;

        $dividendDates = $this->computeDividendDates();
        extract($dividendDates);

        // Approximate Average Monthly Balance (AMB) as current paid-up balance
        // since we don't store month-end snapshots. Flagged as an estimate in the view.
        $averageMonthlyBalance = $currentBalance;
        $projectedNextDividend = round($averageMonthlyBalance * ($dividendRate / 100) / 2, 2);
        $iscAmount = round($projectedNextDividend * self::ISC_SPLIT, 2);
        $patronageAmount = round($projectedNextDividend * self::PATRONAGE_SPLIT, 2);
        $totalDividendsEarned = $dividendHistory->where('status', 'Paid')->sum('dividend_amount');

        $prevDividendDate = $lastDividend
            ? Carbon::parse($lastDividend->date_paid)
            : $nextDividendDate->copy()->subMonths(6);
        $prevDividendPeriod = $lastDividend->period_label
            ?? ($nextDividendSemester === 1
                ? '2nd Semester ' . ($nextDividendDate->year - 1)
                : '1st Semester ' . $nextDividendDate->year);

        $futureDate2 = $nextDividendDate->copy()->addMonths(6);
        $futurePeriod2 = $nextDividendSemester === 1
            ? '2nd Semester ' . $nextDividendDate->year
            : '1st Semester ' . ($nextDividendDate->year + 1);

        $gcashPaymentMethod = \App\Models\PaymentMethod::where('method_name', 'GCash')
            ->where('is_active', true)
            ->first();

        $paymentMethods = \App\Models\PaymentMethod::where('is_active', true)->orderBy('id')->get();

        return view('members_components.share_capital', array_merge(
            ['username' => $username, 'email' => $email],
            compact(
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
                'lastDividend',
                'lastDividendAmount',
                'lastDividendDate',
                'lastDividendPeriod',
                'nextDividendDate',
                'nextDividendPeriod',
                'averageMonthlyBalance',
                'projectedNextDividend',
                'iscAmount',
                'patronageAmount',
                'totalDividendsEarned',
                'prevDividendDate',
                'prevDividendPeriod',
                'futureDate2',
                'futurePeriod2',
                'gcashPaymentMethod',
                'paymentMethods'
            )
        ));
    }

    /**
     * Handle Cash form submission.
     */
    public function store(Request $request)
    {
        $hasQr = \App\Models\PaymentMethod::whereRaw('LOWER(method_name) = ?', [strtolower($request->input('payment_method'))])
            ->where('has_qr_code', true)
            ->exists();

        $validated = $request->validate([
            'shares' => 'required|numeric|min:0.005',
            'type' => ['required', 'in:Deposit,Withdrawal'],
            'payment_method' => ['required', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:500'],
            'gcash_proof' => [($hasQr ? 'required' : 'nullable'), 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'gcash_number' => ['nullable', 'string', 'max:20'],
            'gcash_reference_no' => [
                'nullable',
                function ($attribute, $value, $fail) use ($hasQr) {
                    if ($hasQr && empty($value)) {
                        $fail('The reference number field is required for this payment method.');
                        return;
                    }
                    if (empty($value)) {
                        return;
                    }
                    $value = (string) $value;
                    $alreadyUsed = DB::table('share_capital_transaction_tbls')->where('gcash_reference_no', $value)->where('status', '!=', 'voided')->exists()
                        || DB::table('savings_transaction_tbls')->where('gcash_reference_no', $value)->where('status', '!=', 'voided')->exists()
                        || DB::table('lending_repayments_tbls')->where('gcash_reference_no', $value)->where('status', '!=', 'voided')->exists();
                    if ($alreadyUsed) {
                        $fail('This reference number has already been used for a transaction.');
                    }
                },
                'string',
                'digits:13'
            ],
        ]);

        $gcashNumber = $request->input('gcash_number') ? trim($request->input('gcash_number')) : null;
        $gcashReferenceNo = $request->input('gcash_reference_no') ? trim($request->input('gcash_reference_no')) : null;
        $gcashProofPath = $request->hasFile('gcash_proof')
            ? $request->file('gcash_proof')->store('documents/gcash_proofs', 'public')
            : null;

        $memberId = Auth::id();
        $amountPerShare = self::PAR_VALUE;
        $shares = round((float) $validated['shares'], 6);
        $totalAmount = round($shares * $amountPerShare, 2);
        $now = Carbon::now();
        $referenceNo = $this->generateReferenceNo();
        $type = $validated['type'];

        $account = DB::table('share_capital_account_tbls')
            ->where('user_id', $memberId)
            ->first();

        $currentBalance = $account->total_amount ?? 0;

        // ── Block withdrawal when balance is 0 ──────────────────
        if ($type === 'Withdrawal' && $currentBalance <= 0) {
            return redirect()->back()
                ->with('error', 'You cannot withdraw because your current balance is ₱0.')
                ->withInput();
        }

        // ── Block withdrawal exceeding current balance ──────────
        if ($type === 'Withdrawal' && $totalAmount > $currentBalance) {
            return redirect()->back()
                ->with('error', 'Withdrawal amount (₱' . number_format($totalAmount, 0) . ') exceeds your current balance (₱' . number_format($currentBalance, 0) . ').')
                ->withInput();
        }

        // ── Full withdrawal → auto-resignation ───────────────────
        if ($type === 'Withdrawal' && $totalAmount >= $currentBalance) {
            $existing = ResignationRequest_tbl::where('user_id', $memberId)
                ->whereIn('status', ['pending'])
                ->first();

            if ($existing) {
                return redirect()->back()
                    ->with('error', 'You already have a pending resignation request.')
                    ->withInput();
            }

            DB::beginTransaction();
            try {
                ResignationRequest_tbl::create([
                    'user_id' => $memberId,
                    'withdraw_share_capital' => true,
                    'status' => 'pending',
                ]);

                Users_tbl::where('id', $memberId)->update(['status' => 'resignation_pending']);

                DB::commit();

                return redirect()->route('ShareCapitalMember')
                    ->with('warning', 'Fully withdrawing your share capital requires resigning from the cooperative. Your resignation request has been automatically submitted for approval, subject to the 60-day release rule.');
            } catch (\Throwable $e) {
                DB::rollBack();

                return redirect()->back()
                    ->with('error', 'Failed to process resignation: ' . $e->getMessage())
                    ->withInput();
            }
        }

        DB::beginTransaction();

        try {
            // Every transaction — Deposit or Withdrawal — is held as Pending until
            // an admin approves it. The account row's total_shares/total_amount are
            // NEVER touched here; computeBalanceAndShares() only counts Completed
            // deposits / Approved withdrawals, so nothing should be credited or
            // debited at submission time. The admin-side approval action is what
            // must actually increment/decrement total_shares and total_amount once
            // it flips this transaction's status.
            if ($account) {
                $accountId = $account->id;
            } else {
                // First-ever transaction for this member — create an empty shell
                // account so the transaction has somewhere to attach. Balance stays
                // ₱0 / 0 shares until the admin approves the first deposit.
                $accountId = DB::table('share_capital_account_tbls')->insertGetId([
                    'user_id' => $memberId,
                    'total_shares' => 0,
                    'total_amount' => 0,
                    'status' => 'Active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $transactionStatus = 'Pending';

            DB::table('share_capital_transaction_tbls')->insert([
                'share_capital_account_id' => $accountId,
                'type' => $type,
                'shares' => $shares,
                'amount_per_share' => $amountPerShare,
                'total_amount' => $totalAmount,
                'payment_method' => $validated['payment_method'],
                'reference_no' => $referenceNo,
                'gcash_proof_path' => $gcashProofPath,
                'gcash_number' => $gcashNumber,
                'gcash_reference_no' => $gcashReferenceNo,
                'note' => $validated['note'] ?? null,
                'status' => $transactionStatus,
                'transaction_date' => $now->toDateString(),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::commit();

            AuditLog::log(
                'Member ' . ($type === 'Withdrawal' ? 'Share Capital Withdrawal Request' : 'Share Capital ' . $type),
                ($type === 'Withdrawal' ? 'Requested withdrawal of ' : 'Subscribed ') . $shares . ' shares (₱' . number_format($totalAmount, 2) . ') (Ref: ' . $referenceNo . ')',
                'share_capital',
                $accountId
            );

            $memberName = $this->resolveMemberName();

            return redirect()->route('Financial', ['tab' => 'share_capital'])
                ->with('success', 'Share capital request submitted successfully!')
                ->with('sc_receipt_shares', $shares)
                ->with('sc_receipt_amount', $totalAmount)
                ->with('sc_receipt_method', ucfirst($validated['payment_method']))
                ->with('sc_receipt_ref', $referenceNo)
                ->with('sc_receipt_member', $memberName)
                ->with('sc_receipt_type', $type)
                ->with('sc_receipt_status', $transactionStatus);

        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Something went wrong: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Member converts part of their savings balance into share capital.
     * Only whole shares are converted; the remainder stays in savings.
     */
    public function convertSavingsToShareCapital(Request $request)
    {

        if ((int) session('convert_pw_verified_until', 0) < now()->timestamp) {
            return response()->json([
                'success' => false,
                'message' => 'Password confirmation required.'
            ], 403);
        }
        session()->forget('convert_pw_verified_until');

        $request->validate([
            'amount' => 'required|numeric|min:' . self::PAR_VALUE,
        ]);

        $memberId = Auth::id();
        $price = self::PAR_VALUE;
        $requested = round((float) $request->amount, 2);

        // Whole shares only
        $shares = (int) floor(($requested / $price) + 1e-9);
        $cost = $shares * $price;

        if ($shares < 1) {
            return response()->json([
                'success' => false,
                'message' => 'The minimum conversion is ₱' . $price . ' (1 share).',
            ], 422);
        }

        try {
            $result = DB::transaction(function () use ($memberId, $shares, $cost, $price) {
                $now = Carbon::now();

                // Lock the savings row so two requests can't spend the same money
                $savings = DB::table('savings_account_tbls')
                    ->where('user_id', $memberId)
                    ->lockForUpdate()
                    ->first();

                if (!$savings) {
                    throw new \RuntimeException('You do not have a savings account.');
                }

                if ((float) $savings->balance < $cost) {
                    throw new \RuntimeException(
                        'That is more than your savings balance (₱' . number_format($savings->balance, 2) . ').'
                    );
                }

                $scAccount = DB::table('share_capital_account_tbls')
                    ->where('user_id', $memberId)
                    ->lockForUpdate()
                    ->first();

                if ($scAccount) {
                    $scAccountId = $scAccount->id;
                } else {
                    $scAccountId = DB::table('share_capital_account_tbls')->insertGetId([
                        'user_id' => $memberId,
                        'total_shares' => 0,
                        'total_amount' => 0,
                        'status' => 'Active',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }

                $ref = 'CNV-' . strtoupper(uniqid()) . '-' . $now->format('Ymd');

                // 1) Debit savings
                $newSavings = round((float) $savings->balance - $cost, 2);

                DB::table('savings_account_tbls')
                    ->where('id', $savings->id)
                    ->update(['balance' => $newSavings, 'updated_at' => $now]);

                DB::table('savings_transaction_tbls')->insert([
                    'savings_account_id' => $savings->id,
                    'type' => 'withdrawal',
                    'amount' => $cost,
                    'balance_after' => $newSavings,
                    'reference_no' => $ref,
                    'payment_method' => 'Conversion',
                    'transaction_date' => $now->toDateString(),
                    'status' => 'Completed',
                    'created_by' => $memberId,
                    'approved_by' => $memberId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                // 2) Credit share capital (ledger row + account totals)
                DB::table('share_capital_account_tbls')
                    ->where('id', $scAccountId)
                    ->update([
                        'total_shares' => DB::raw('total_shares + ' . (int) $shares),
                        'total_amount' => DB::raw('total_amount + ' . (float) $cost),
                        'updated_at' => $now,
                    ]);

                DB::table('share_capital_transaction_tbls')->insert([
                    'share_capital_account_id' => $scAccountId,
                    'type' => self::CONVERSION_TYPE,
                    'shares' => $shares,
                    'amount_per_share' => $price,
                    'total_amount' => $cost,
                    'payment_method' => 'Savings',
                    'reference_no' => $ref,
                    'note' => 'Converted from savings',
                    'status' => 'Completed',
                    'transaction_date' => $now->toDateString(),
                    'created_by' => $memberId,
                    'approved_by' => $memberId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                AuditLog::log(
                    'Converted Savings to Share Capital',
                    "Converted ₱{$cost} from savings into {$shares} share(s) (Ref: {$ref})",
                    'share_capital',
                    $scAccountId
                );

                return ['shares' => $shares, 'amount' => $cost, 'reference_no' => $ref];
            });

            return response()->json(['success' => true] + $result);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            Log::error('Savings to share capital conversion failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'The conversion could not be completed.',
            ], 500);
        }
    }

    /**
     * Show the Share Capital form for a specific member via email link.
     */
    public function showForMember($id)
    {
        $id = (int) $id;

        if (!Auth::check()) {
            return redirect()->guest(route('login'));
        }

        abort_unless((int) Auth::id() === $id || \App\Services\SoDGuard::actingAsStaff(), 403);

        $user = \App\Models\Users_tbl::findOrFail($id);

        $account = DB::table('share_capital_account_tbls')
            ->where('user_id', $id)
            ->first();

        if ($account) {
            [$currentBalance, $currentShares] = $this->computeBalanceAndShares($account);
        } else {
            $currentBalance = 0;
            $currentShares = 0;
        }

        $dividendRateRecord = $this->getDividendRateRecord();
        $dividendRate = $dividendRateRecord->rate ?? 8.5;

        return view('ShareCapitalForm.share_capital_form', compact(
            'currentBalance',
            'currentShares',
            'dividendRate',
            'user'
        ));
    }

    /**
     * Sell/transfer shares from one member to another (admin only).
     */
    public function sellShares(Request $request)
    {
        $request->validate([
            'seller_id' => 'required|exists:users_tbls,id',
            'buyer_id' => 'required|exists:users_tbls,id|different:seller_id',
            'shares' => 'required|numeric|min:0.5',
            'amount' => 'required|numeric|min:1000',
        ], [
            'amount.min' => 'The transfer amount must be at least ₱1,000.',
        ]);

        $sellerId = (int) $request->seller_id;
        $buyerId = (int) $request->buyer_id;
        $shares = (float) $request->shares;
        $totalAmount = (float) $request->amount;
        $amountPerShare = self::PAR_VALUE;
        $now = Carbon::now();

        DB::beginTransaction();
        try {
            $sellerAccount = DB::table('share_capital_account_tbls')
                ->where('user_id', $sellerId)
                ->first();

            if (!$sellerAccount || (float) $sellerAccount->total_shares < $shares) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient shares. Seller has ' . ($sellerAccount->total_shares ?? 0) . ' shares.',
                ], 422);
            }

            DB::table('share_capital_account_tbls')
                ->where('user_id', $sellerId)
                ->update([
                    'total_shares' => (float) $sellerAccount->total_shares - $shares,
                    'total_amount' => (float) $sellerAccount->total_amount - $totalAmount,
                    'updated_at' => $now,
                ]);

            $buyerAccount = DB::table('share_capital_account_tbls')
                ->where('user_id', $buyerId)
                ->first();

            if ($buyerAccount) {
                DB::table('share_capital_account_tbls')
                    ->where('user_id', $buyerId)
                    ->update([
                        'total_shares' => (float) $buyerAccount->total_shares + $shares,
                        'total_amount' => (float) $buyerAccount->total_amount + $totalAmount,
                        'status' => 'Active',
                        'updated_at' => $now,
                    ]);
                $buyerAccountId = $buyerAccount->id;
            } else {
                $buyerAccountId = DB::table('share_capital_account_tbls')->insertGetId([
                    'user_id' => $buyerId,
                    'total_shares' => $shares,
                    'total_amount' => $totalAmount,
                    'status' => 'Active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $refNo = 'TRF-' . strtoupper(uniqid()) . '-' . now()->format('Ymd');

            DB::table('share_capital_transaction_tbls')->insert([
                'share_capital_account_id' => $sellerAccount->id,
                'type' => 'Withdrawal',
                'shares' => $shares,
                'amount_per_share' => $amountPerShare,
                'total_amount' => $totalAmount,
                'payment_method' => 'transfer',
                'reference_no' => $refNo,
                'note' => 'Transferred to member #' . $buyerId . ' (share transfer)',
                'status' => 'Completed',
                'transaction_date' => $now->toDateString(),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('share_capital_transaction_tbls')->insert([
                'share_capital_account_id' => $buyerAccountId,
                'type' => 'Deposit',
                'shares' => $shares,
                'amount_per_share' => $amountPerShare,
                'total_amount' => $totalAmount,
                'payment_method' => 'transfer',
                'reference_no' => $refNo,
                'note' => 'Transferred from member #' . $sellerId . ' (share transfer)',
                'status' => 'Completed',
                'transaction_date' => $now->toDateString(),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::commit();

            $seller = DB::table('users_tbls')->where('id', $sellerId)->first();
            $buyer = DB::table('users_tbls')->where('id', $buyerId)->first();
            AuditLog::log(
                'Transferred Share Capital',
                "Transferred {$shares} shares (₱{$totalAmount}) from {$seller?->first_name} {$seller?->last_name} (#{$sellerId}) to {$buyer?->first_name} {$buyer?->last_name} (#{$buyerId}) (Ref: {$refNo})",
                'share_capital_transfer',
                $sellerId
            );

            return response()->json([
                'success' => true,
                'message' => number_format($shares, 0) . ' shares (₱' . number_format($totalAmount, 2) . ') transferred successfully!',
                'reference_no' => $refNo,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Transfer failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Compute balance/shares consistently (Completed deposits minus Approved withdrawals).
     */
    public function computeBalanceAndShares($account): array
    {
        $stats = self::paidUpForAccount($account->id);

        return [$stats['amount'], $stats['shares']];
    }

    /**
     * Paid-up share capital stats for a single account, derived from the ledger.
     * Shares/amount = Completed Deposit/Subscription/Conversion totals minus
     * Approved Withdrawal totals. Single source of truth shared by the member
     * and admin sides so both report identical numbers.
     */
    public static function paidUpForAccount(int $accountId): array
    {
        $accounts = self::paidUpForAccounts([$accountId]);

        return $accounts[$accountId] ?? ['shares' => 0.0, 'amount' => 0.0];
    }

    /**
     * Bulk variant of paidUpForAccount() using grouped SUM queries.
     * Returns a map of accountId => ['shares' => float, 'amount' => float].
     */
    public static function paidUpForAccounts(array $accountIds): array
    {
        $accountIds = array_values(array_unique(array_map('intval', $accountIds)));

        if (empty($accountIds)) {
            return [];
        }

        $deposits = DB::table('share_capital_transaction_tbls')
            ->whereIn('share_capital_account_id', $accountIds)
            ->whereIn('type', ['Deposit', 'Subscription', self::CONVERSION_TYPE])
            ->whereIn('status', ['Completed', 'completed'])
            ->groupBy('share_capital_account_id')
            ->selectRaw('share_capital_account_id as id, SUM(shares) as shares, SUM(total_amount) as amount')
            ->get()
            ->keyBy('id');

        $withdrawals = DB::table('share_capital_transaction_tbls')
            ->whereIn('share_capital_account_id', $accountIds)
            ->where('type', 'Withdrawal')
            ->whereIn('status', ['Approved', 'approved'])
            ->groupBy('share_capital_account_id')
            ->selectRaw('share_capital_account_id as id, SUM(shares) as shares, SUM(total_amount) as amount')
            ->get()
            ->keyBy('id');

        $result = [];

        foreach ($accountIds as $accountId) {
            $deposit = $deposits->get($accountId);
            $withdrawal = $withdrawals->get($accountId);

            $result[$accountId] = [
                'shares' => (float) ($deposit->shares ?? 0) - (float) ($withdrawal->shares ?? 0),
                'amount' => (float) ($deposit->amount ?? 0) - (float) ($withdrawal->amount ?? 0),
            ];
        }

        return $result;
    }

    /**
     * Build the 8-quarter (2-year) Capital Build-Up installment timeline.
     * Each slot represents a real 3-month calendar window starting from the
     * account's creation date. All completed Deposit/Subscription
     * transactions that fall inside a window are SUMMED into that slot —
     * so multiple small deposits in the same quarter don't spill into
     * later slots. Anything paid after the 8-quarter plan window ends
     * (or once the target is already met) is reported separately as an
     * "advance payment" rather than being silently dropped.
     * No new columns required — this is fully derived from transaction_date.
     */
    public function buildInstallmentTimeline($account, $contributions): array
    {
        if (!$account) {
            $slots = [];
            for ($i = 1; $i <= self::INSTALLMENT_SLOTS; $i++) {
                $year = $i <= 4 ? 1 : 2;
                $quarter = (($i - 1) % 4) + 1;
                $slots[] = [
                    'index' => $i,
                    'label' => "Q{$quarter}",
                    'sublabel' => "Year {$year}",
                    'status' => 'upcoming',
                    'amount' => null,
                    'date' => null,
                ];
            }

            return ['slots' => $slots, 'advance' => 0];
        }

        $paid = collect($contributions)
            ->whereIn('type', ['Deposit', 'Subscription', self::CONVERSION_TYPE])
            ->filter(fn($c) => strtolower($c->status ?? '') === 'completed')
            ->map(fn($c) => (object) [
                'amount' => (float) $c->total_amount,
                'date' => Carbon::parse($c->transaction_date),
            ])
            ->sortBy('date')
            ->values();

        // Anchor the plan's day-one to whichever is EARLIER: the account
        // row's created_at, or the member's first completed contribution.
        // Without this, transactions dated before the account row was
        // created/seeded would fall before window 1 and never match any
        // slot — which is what was causing everything to show "Upcoming"
        // despite a fully paid-up balance.
        $startDate = Carbon::parse($account->created_at);
        if ($paid->isNotEmpty() && $paid->first()->date->lt($startDate)) {
            $startDate = $paid->first()->date->copy();
        }

        $planEnd = $startDate->copy()->addMonths(self::INSTALLMENT_SLOTS * 3);

        $quartersElapsed = (int) ceil(max(1, $startDate->diffInMonths(Carbon::today()) / 3));

        $slots = [];
        $firstUnpaidFound = false;

        for ($i = 1; $i <= self::INSTALLMENT_SLOTS; $i++) {
            $year = $i <= 4 ? 1 : 2;
            $quarter = (($i - 1) % 4) + 1;
            $windowStart = $startDate->copy()->addMonths(($i - 1) * 3);
            $windowEnd = $startDate->copy()->addMonths($i * 3);

            $inWindow = $paid->filter(fn($c) => $c->date->gte($windowStart) && $c->date->lt($windowEnd));
            $sum = $inWindow->sum('amount');

            if ($sum > 0) {
                $status = 'paid';
                $amount = $sum;
                $date = $inWindow->last()->date;
            } elseif (!$firstUnpaidFound && $quartersElapsed >= $i) {
                $status = 'due';
                $amount = null;
                $date = $windowStart;
                $firstUnpaidFound = true;
            } else {
                $status = 'upcoming';
                $amount = null;
                $date = $windowStart;
            }

            $slots[] = [
                'index' => $i,
                'label' => "Q{$quarter}",
                'sublabel' => "Year {$year}",
                'status' => $status,
                'amount' => $amount,
                'date' => $date,
            ];
        }

        // Anything paid after the 8-quarter plan window closes is an
        // advance/overflow payment — never dropped, just reported separately.
        $advance = $paid->filter(fn($c) => $c->date->gte($planEnd))->sum('amount');

        return ['slots' => $slots, 'advance' => $advance];
    }

    public function computeDividendDates(): array
    {
        $today = Carbon::today();
        $jun15ThisYear = Carbon::create($today->year, 6, 15);
        $dec15ThisYear = Carbon::create($today->year, 12, 15);
        $jun15NextYear = Carbon::create($today->year + 1, 6, 15);

        if ($today->lte($jun15ThisYear)) {
            $nextDividendDate = $jun15ThisYear;
            $nextDividendPeriod = '1st Semester ' . $today->year;
            $nextDividendSemester = 1;
        } elseif ($today->lte($dec15ThisYear)) {
            $nextDividendDate = $dec15ThisYear;
            $nextDividendPeriod = '2nd Semester ' . $today->year;
            $nextDividendSemester = 2;
        } else {
            $nextDividendDate = $jun15NextYear;
            $nextDividendPeriod = '1st Semester ' . ($today->year + 1);
            $nextDividendSemester = 1;
        }

        return compact('nextDividendDate', 'nextDividendPeriod', 'nextDividendSemester');
    }

    public function getDividendRateRecord()
    {
        if ($this->tableExists('dividend_rates_tbls')) {
            return DB::table('dividend_rates_tbls')
                ->orderByDesc('effective_year')
                ->orderByDesc('created_at')
                ->first();
        }

        return null;
    }

    public function getRateHistory()
    {
        if ($this->tableExists('dividend_rates_tbls')) {
            return DB::table('dividend_rates_tbls')
                ->orderByDesc('effective_year')
                ->limit(5)
                ->get();
        }

        return collect();
    }

    public function getDividendHistory($accountId)
    {
        if ($this->tableExists('dividend_histories_tbls')) {
            return DB::table('dividend_histories_tbls')
                ->where('share_capital_account_id', $accountId)
                ->orderByDesc('year')
                ->orderByDesc('semester')
                ->get();
        }

        return collect();
    }

    private function generateReferenceNo(): string
    {
        return 'SC-' . strtoupper(uniqid()) . '-' . now()->format('Ymd');
    }

    private function resolveMemberName(): string
    {
        $name = trim((Auth::user()->first_name ?? '') . ' ' . (Auth::user()->last_name ?? ''));

        return $name ?: (Auth::user()->name ?? Auth::user()->username ?? 'Member');
    }

    private function tableExists(string $table): bool
    {
        try {
            return DB::getSchemaBuilder()->hasTable($table);
        } catch (\Throwable) {
            return false;
        }
    }
}
