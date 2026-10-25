<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\lending_program_tbl;
use App\Models\lending_repayments_tbl;
use App\Models\lending_status_tbl;
use App\Models\Loan_settings_tbl;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class lendingController extends Controller
{
    const PAYMENT_INTERVAL_MONTHS = 1;

    /**
     * Due date for installment offset $n from an anchor date.
     * addMonthsNoOverflow keeps month-end dates sane (Jan 31 -> Feb 28, not Mar 3).
     */
    private function dueDateFromAnchor(\Carbon\Carbon $anchor, int $offset): \Carbon\Carbon
    {
        return $anchor->copy()->addMonthsNoOverflow($offset * self::PAYMENT_INTERVAL_MONTHS);
    }

    private function loanChargeTotals($loan): array
    {
        $feesOnly = round(
            (float) ($loan->processing_fee_rate ?? 0)
            + (float) ($loan->service_fee_rate ?? 0)
            + (float) ($loan->loan_protection_fee ?? 0)
            + (float) ($loan->retention_paid_rate ?? 0)
            + (float) ($loan->retention_unpaid_rate ?? 0),
            2
        );

        $interest = (float) ($loan->total_interest ?? 0);
        $principal = (float) $loan->lending_amount;
        $storedPayable = (float) ($loan->total_payment ?? $principal);
        $principalPlusInterest = round($principal + $interest, 2);

        $feesAlreadyIncluded = abs($storedPayable - $principalPlusInterest) > 0.01;

        return [
            'fees_only' => $feesOnly,
            'total_charges' => round($interest + $feesOnly, 2), // now: Interest + Fees, everywhere
            'total_payable' => $feesAlreadyIncluded
                ? round($storedPayable, 2)
                : round($principalPlusInterest + $feesOnly, 2),
        ];
    }

    /**
     * Single source of truth for a loan's display status (Active / Overdue / Completed).
     * Used by BOTH the loan grid cards and the loan detail hero so they can never
     * disagree on the same loan's status again.
     */
    private function resolveLoanStatusLabel($loan, $status): string
    {
        if ($loan->status === 'Completed') {
            return 'Completed';
        }

        if (!$status) {
            return 'Active';
        }

        $totalPayments = (int) ($status->total_payments ?? 0);
        $paymentsMade = (int) ($status->payments_made ?? 0);

        if ($totalPayments <= 0 || $paymentsMade >= $totalPayments) {
            return 'Active';
        }

        $today = now()->timezone('Asia/Manila');

        $scheduleByNumber = DB::table('lending_installment_schedules_tbls')
            ->where('lending_id', $loan->id)
            ->get()
            ->keyBy('payment_number');

        $nextInstallmentNumber = $paymentsMade + 1;
        $anchorDueDate = $status->due_date
            ? \Carbon\Carbon::parse($status->due_date)
            : \Carbon\Carbon::parse($loan->created_at)->addMonthsNoOverflow($nextInstallmentNumber * self::PAYMENT_INTERVAL_MONTHS);

        for ($i = 1; $i <= $totalPayments; $i++) {
            $row = $scheduleByNumber[$i] ?? null;
            $isPaid = $row
                ? (float) $row->amount_paid >= (float) $row->amount_due
                : $i <= $paymentsMade;

            if ($isPaid) {
                continue;
            }

            $dueDateForRow = $this->dueDateFromAnchor($anchorDueDate, $i - $nextInstallmentNumber);

            return $dueDateForRow->lt($today) ? 'Overdue' : 'Active';
        }

        return 'Active';
    }

    // ─── Shared helper ────────────────────────────────────────────────────────────
    private function getLoanPageData(): array
    {
        $memberId = auth()->id();

        $savedMonthlyIncome = DB::table('lending_program_tbls')
            ->where('user_id', $memberId)
            ->whereNotNull('monthly_income')
            ->orderBy('created_at', 'desc')
            ->value('monthly_income');

        $savingsAccount = DB::table('savings_account_tbls')
            ->where('user_id', $memberId)->first();

        $currentSavings = (float) ($savingsAccount->balance ?? 0);

        $loanEligSettings = \App\Models\LoanEligibilitySetting::getOrCreate();

        // Savings holdback (flat pesos): max loan = savings − holdback
        $savingsHoldback = $loanEligSettings->savings_to_loan_enabled
            ? (float) $loanEligSettings->savings_to_loan_ratio
            : 0;

        // Share capital requirement: minimum paid-up shares, from the ledger
        $scAccount = DB::table('share_capital_account_tbls')
            ->where('user_id', $memberId)
            ->first();

        $currentShares = $scAccount
            ? (float) ShareCapital::paidUpForAccount((int) $scAccount->id)['shares']
            : 0;

        $minimumShares = (float) ($loanEligSettings->minimum_shares ?? 10);

        $canApplyLoan = $currentShares >= $minimumShares;
        $maxLoan = 25000;

        // Savings caps total borrowing capacity, regardless of the ₱25,000 program ceiling
        $maxLoanBySavings = max(0, $currentSavings - $savingsHoldback);

        // Get all active loans (Pending, Approved, Completed) and calculate remaining balance
        // Using lending_status_tbl to get actual remaining balance instead of original loan amount
        $loans = DB::table('lending_program_tbls')
            ->where('user_id', $memberId)
            ->whereIn('status', ['Pending', 'Approved', 'Completed'])
            ->get();

        // Count only the principal (lending_amount) - the actual loan amount borrowed, excluding interest
        $totalActiveLoan = 0;
        $totalPaidOnActiveLoans = 0;

        $approvedLoans = DB::table('lending_program_tbls as l')
            ->leftJoin('lending_status_tbls as s', 's.lending_id', '=', 'l.id')
            ->where('l.user_id', $memberId)
            ->where('l.status', 'Approved')
            ->select('l.*', 's.due_date', 's.remaining_balance', 's.status as loan_status')
            ->get();

        $typeMapApproved = [
            'Personal Lending' => 'Personal Loan',
            'Emergency Lending' => 'Emergency Loan',
            'Business Lending' => 'Business Loan',
            'Education Lending' => 'Education Loan',
        ];

        $approvedLoans = $approvedLoans->map(function ($loan) use ($typeMapApproved) {
            $loan->lending_type = $typeMapApproved[$loan->lending_type] ?? $loan->lending_type;

            // These columns aren't selected in this query — total_payments/payments_made
            // come from lending_status_tbls but weren't pulled here, so pull them too.
            $status = DB::table('lending_status_tbls')->where('lending_id', $loan->id)->first();
            $totalPayments = (int) ($status->total_payments ?? 0);
            $paymentsMade = (int) ($status->payments_made ?? 0);

            $loan->total_payments = $totalPayments;
            $loan->payments_made = $paymentsMade;

            $totals = $this->loanChargeTotals($loan);
            $loan->total_charges = $totals['total_charges'];
            $loan->total_payable = $totals['total_payable'];

            $loan->monthly_payment = $totalPayments > 0
                ? round($loan->total_payable / $totalPayments, 2)
                : 0;

            return $loan;
        });

        $today = now()->timezone('Asia/Manila')->toDateString();
        $weekEnd = now()->timezone('Asia/Manila')->addDays(7)->toDateString();

        $dueTodayCount = $approvedLoans->filter(
            fn($l) => $l->due_date && $l->due_date === $today && ($l->remaining_balance ?? 0) > 0
        )->count();

        $dueThisWeekCount = $approvedLoans->filter(
            fn($l) => $l->due_date && $l->due_date > $today && $l->due_date <= $weekEnd && ($l->remaining_balance ?? 0) > 0
        )->count();

        $overdueCount = $approvedLoans->filter(
            fn($l) => $l->due_date && $l->due_date < $today && ($l->remaining_balance ?? 0) > 0
        )->count();

        // All loans (any status) for the table
        $allLoans = DB::table('lending_program_tbls as l')
            ->leftJoin('lending_status_tbls as s', 's.lending_id', '=', 'l.id')
            ->where('l.user_id', $memberId)
            ->orderByRaw("CASE WHEN l.status = 'Pending' THEN 0 ELSE 1 END")
            ->orderBy('l.created_at', 'desc')
            ->select(
                'l.*',
                's.due_date',
                's.remaining_balance',
                's.total_paid',
                's.payments_made',
                's.total_payments'
            )
            ->get()
            ->map(function ($loan) use ($today, $weekEnd) {
                $typeMap = [
                    'Personal Lending' => 'Personal Loan',
                    'Emergency Lending' => 'Emergency Loan',
                    'Business Lending' => 'Business Loan',
                    'Education Lending' => 'Education Loan',
                ];
                $loan->lending_type = $typeMap[$loan->lending_type] ?? $loan->lending_type;

                $totalPayments = (int) ($loan->total_payments ?? 0);
                $paymentsMade = (int) ($loan->payments_made ?? 0);

                $loan->total_payments = $totalPayments;
                $loan->payments_made = $paymentsMade;
                $loan->progress_percent = $totalPayments > 0
                    ? min(100, round(($paymentsMade / $totalPayments) * 100))
                    : 0;

                // ── NEW: charge totals, computed the same way loanStatus() does ──
                $totals = $this->loanChargeTotals($loan);
                $loan->total_charges = $totals['total_charges'];
                $loan->total_payable = $totals['total_payable'];

                // Per-installment now reflects the charge-inclusive payable
                $loan->monthly_payment = $totalPayments > 0
                    ? round($loan->total_payable / $totalPayments, 2)
                    : 0;

                // Due tag (today / week / overdue) — only for active balances
                $loan->due_category = null;
                if ($loan->status === 'Approved' && $loan->due_date && ($loan->remaining_balance ?? 0) > 0) {
                    if ($loan->due_date === $today) {
                        $loan->due_category = 'today';
                    } elseif ($loan->due_date > $today && $loan->due_date <= $weekEnd) {
                        $loan->due_category = 'week';
                    } elseif ($loan->due_date < $today) {
                        $loan->due_category = 'overdue';
                    }
                }

                return $loan;
            });

        $allLoansCount = $allLoans->count();

        foreach ($loans as $loan) {
            // Skip Completed loans - they're fully paid
            if ($loan->status === 'Completed') {
                continue;
            }

            // Use lending_amount (principal only) - not total_payment which includes interest
            $principal = (float) $loan->lending_amount;

            $status = DB::table('lending_status_tbls')
                ->where('lending_id', $loan->id)
                ->first();

            if ($status && $principal > 0) {
                $totalPaid = isset($status->total_paid) ? (float) $status->total_paid : 0;

                // Get total payment (principal + interest)
                $totalPayment = isset($loan->total_payment) ? (float) $loan->total_payment : $principal;
                $remainingBalance = isset($status->remaining_balance) ? (float) $status->remaining_balance : $principal;

                // Calculate remaining principal proportionally
                // remaining_balance/total_payment = remaining_principal/principal
                if ($totalPayment > 0 && $remainingBalance > 0) {
                    $principalRemaining = ($remainingBalance / $totalPayment) * $principal;
                } else {
                    $principalRemaining = $principal;
                }

                // Cap at original principal
                $principalRemaining = min($principal, max(0, $principalRemaining));

                if ($principalRemaining > 0.01) {
                    $totalActiveLoan += $principalRemaining;
                }
                $totalPaidOnActiveLoans += $totalPaid;
            } else {
                // No status yet - use full principal as active
                if ($principal > 0) {
                    $totalActiveLoan += $principal;
                }
            }
        }

        $effectiveCeiling = min($maxLoan, $maxLoanBySavings);
        $remainingLoanable = max(0, $effectiveCeiling - $totalActiveLoan);

        $remainingLoanableCents = (int) round($remainingLoanable * 100);
        $effectiveCeilingCents = (int) round($effectiveCeiling * 100);

        if ($remainingLoanableCents >= $effectiveCeilingCents - 100 || $totalActiveLoan < 0.02) {
            $remainingLoanable = $effectiveCeiling;
        } else {
            $remainingLoanable = $remainingLoanableCents / 100;
        }

        $hasFullyLoaned = $totalActiveLoan >= $effectiveCeiling;

        // Due today loans (full records)
        $dueTodayLoans = $approvedLoans->filter(
            fn($l) => $l->due_date && $l->due_date === $today && ($l->remaining_balance ?? 0) > 0
        )->values();

        // Due this week loans (full records)
        $dueThisWeekLoans = $approvedLoans->filter(
            fn($l) => $l->due_date && $l->due_date > $today && $l->due_date <= $weekEnd && ($l->remaining_balance ?? 0) > 0
        )->values();

        // Overdue loans (full records)
        $overdueLoans = $approvedLoans->filter(
            fn($l) => $l->due_date && $l->due_date < $today && ($l->remaining_balance ?? 0) > 0
        )->values();

        return compact(
            'currentSavings',
            'currentShares',
            'minimumShares',
            'maxLoanBySavings',
            'savingsHoldback',
            'canApplyLoan',
            'loanEligSettings',
            'totalActiveLoan',
            'remainingLoanable',
            'hasFullyLoaned',
            'effectiveCeiling',
            'totalPaidOnActiveLoans',
            'savedMonthlyIncome',
            // ── new ──
            'allLoans',
            'allLoansCount',
            'dueTodayCount',
            'dueThisWeekCount',
            'overdueCount',
            'dueTodayLoans',
            'dueThisWeekLoans',
            'overdueLoans'
        );
    }

    // ─── GET: Loan Application page ───────────────────────────────────────────────
    public function index()
    {
        // $this->autoProcessOverdueLoans();
        $username = Auth::check() ? Auth::user()->username : null;
        $email = Auth::check() ? Auth::user()->email : null;

        // Pull the FULL settings row per loan type, not just interest_rate
        $dbSettings = DB::table('loan_settings_tbls')
            ->where('is_active', true)
            ->orderBy('loan_type')
            ->get();

        $loanSettings = [];
        foreach ($dbSettings as $s) {
            // Key by loan_type — this MUST match the <option value="..."> used
            // in $mOptData() inside the blade
            $loanSettings[$s->loan_type] = [
                'interest_rate' => ((float) ($s->interest_rate ?? 2.00)) / 100,
                'processing_fee_rate' => $s->processing_fee_rate ?? 0,
                'service_fee_rate' => $s->service_fee_rate ?? 0,
                'loan_protection_fee' => $s->loan_protection_fee ?? 0,
                'retention_unpaid_rate' => $s->retention_unpaid_rate ?? 0,
            ];
        }

        return view(
            'members_components.loan_application',
            array_merge(
                ['username' => $username, 'email' => $email, 'loanSettings' => $loanSettings],
                $this->getLoanPageData()
            )
        );
    }

    // ─── POST: Submit loan application ────────────────────────────────────────────
    // ─── POST: Submit loan application ────────────────────────────────────────────
    public function lendingProgram(Request $request)
    {
        $memberId = auth()->id();
        $maxLoan = 25000;

        // Share capital requirement: minimum paid-up shares, from the ledger
        $scAccount = DB::table('share_capital_account_tbls')
            ->where('user_id', $memberId)->first();

        $currentShares = $scAccount
            ? (float) ShareCapital::paidUpForAccount((int) $scAccount->id)['shares']
            : 0;

        $savingsAccount = DB::table('savings_account_tbls')->where('user_id', $memberId)->first();
        $currentSavings = (float) ($savingsAccount->balance ?? 0);

        $loanEligSettings = \App\Models\LoanEligibilitySetting::getOrCreate();
        $savingsToLoanEnabled = $loanEligSettings->savings_to_loan_enabled;

        $minimumShares = (float) ($loanEligSettings->minimum_shares ?? 10);
        if ($currentShares < $minimumShares) {
            return redirect()->back()->with(
                'loan_blocked',
                'You need at least ' . number_format($minimumShares, 0) . ' shares of share capital to apply for a loan. You currently have ' . number_format($currentShares, 2) . ' shares.'
            );
        }

        if ($savingsToLoanEnabled) {
            $holdback = (float) $loanEligSettings->savings_to_loan_ratio;
            $requiredSavings = (float) $request->lending_amount + $holdback;
            if ($currentSavings < $requiredSavings) {
                return redirect()->back()->with(
                    'loan_blocked',
                    'Borrowing ₱' . number_format($request->lending_amount, 2) .
                    ' requires ₱' . number_format($requiredSavings, 2) . ' in savings (₱' . number_format($holdback, 2) .
                    ' holdback must remain in savings). You currently have ₱' . number_format($currentSavings, 2) . '.'
                );
            }
        }

        // Use remaining balance from lending_status_tbl instead of original loan amount
        $loans = DB::table('lending_program_tbls')
            ->where('user_id', $memberId)
            ->whereIn('status', ['Pending', 'Approved', 'Completed'])
            ->get();

        // Count only principal (lending_amount), excluding interest
        $totalActiveLoan = 0;

        foreach ($loans as $loan) {
            if ($loan->status === 'Completed') {
                continue;
            }

            $principal = (float) $loan->lending_amount;
            $status = DB::table('lending_status_tbls')
                ->where('lending_id', $loan->id)
                ->first();

            if ($status && $principal > 0) {
                $totalPayment = isset($loan->total_payment) ? (float) $loan->total_payment : $principal;
                $remainingBalance = isset($status->remaining_balance) ? (float) $status->remaining_balance : $principal;

                if ($totalPayment > 0 && $remainingBalance > 0) {
                    $principalRemaining = ($remainingBalance / $totalPayment) * $principal;
                } else {
                    $principalRemaining = $principal;
                }
                $principalRemaining = min($principal, max(0, $principalRemaining));

                if ($principalRemaining > 0.01) {
                    $totalActiveLoan += $principalRemaining;
                }
            } else {
                if ($principal > 0) {
                    $totalActiveLoan += $principal;
                }
            }
        }

        $maxLoanBySavings = $savingsToLoanEnabled
            ? max(0, $currentSavings - (float) $loanEligSettings->savings_to_loan_ratio)
            : 0;
        $effectiveCeiling = min($maxLoan, $maxLoanBySavings);
        $effectiveCeilingCents = (int) round($effectiveCeiling * 100);

        $remainingLoanable = max(0, $effectiveCeiling - $totalActiveLoan);
        $remainingLoanableCents = (int) round($remainingLoanable * 100);

        if ($remainingLoanableCents >= $effectiveCeilingCents - 100 || $totalActiveLoan < 0.02) {
            $remainingLoanable = $effectiveCeiling;
        } else {
            $remainingLoanable = $remainingLoanableCents / 100;
        }

        if ($totalActiveLoan >= $effectiveCeiling) {
            return redirect()->back()
                ->with(
                    'loan_blocked',
                    'You have reached the maximum loan limit of ₱' . number_format($effectiveCeiling, 2) . '. ' .
                    'Please repay your existing loan before applying again.'
                );
        }

        if ($request->lending_amount > $remainingLoanable) {
            return redirect()->back()
                ->with(
                    'loan_blocked',
                    'You can only borrow up to ₱' . number_format($remainingLoanable, 2) .
                    ' more based on your current active loans and savings.'
                )
                ->withInput();
        }

        // Use cents comparison to avoid floating point issues
        $requestedCents = (int) ($request->lending_amount * 100);
        $maxLoanCents = (int) ($maxLoan * 100);

        if ($requestedCents > $maxLoanCents) {
            return redirect()->back()
                ->with('loan_blocked', 'The maximum loan amount allowed is ₱25,000.')
                ->withInput();
        }

        // ── Loan term validation per lending type ────────────────────────────────
        $lendingType = $request->lending_type;
        $allowedTerms = match ($lendingType) {
            'Business Loan' => ['6 months', '12 months'],
            default => ['6 months'],   // Personal, Emergency, Education
        };

        if (!in_array($request->lending_type_term, $allowedTerms)) {
            return redirect()->back()
                ->with(
                    'loan_blocked',
                    'Invalid loan term selected for ' . $lendingType . '. ' .
                    'Allowed: ' . implode(', ', $allowedTerms) . '.'
                )
                ->withInput();
        }

        // ── Fetch fee settings for this loan type ─────────────────────────────────
        $settings = DB::table('loan_settings_tbls')
            ->where('loan_type', $lendingType)
            ->where('is_active', true)
            ->first();

        if (!$settings) {
            return redirect()->back()
                ->with('loan_blocked', 'Loan settings are not configured for this loan type. Please contact the admin.')
                ->withInput();
        }

        try {
            $request->validate([
                'lending_type' => 'nullable|string',
                'lending_amount' => 'nullable|numeric',
                'lending_type_term' => 'nullable|string',
                'monthly_income' => 'nullable|numeric',
                'purpose_loan' => 'nullable|string',
                'purpose_loan_others' => 'nullable|string|required_if:purpose_loan,Others',
                'net_proceeds_adjustment_type' => 'nullable|string|in:add,deduct',
                'personal_valid_id' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
                'personal_proof_of_income' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
                'emergency_valid_id' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
                'emergency_proof_of_income' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
                'proof_of_emergency' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
                'business_valid_id' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
                'business_proof_of_income' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
                'business_permit' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
                'financial_statement' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
                'education_valid_id' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
                'school_id' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
                'cor' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            ]);

            $storeFile = function ($field, $folder) use ($request) {
                if ($request->hasFile($field)) {
                    return $request->file($field)->store("documents/{$folder}", 'public');
                }

                return null;
            };

            $validIdField = match ($lendingType) {
                'Personal Loan' => 'personal_valid_id',
                'Emergency Loan' => 'emergency_valid_id',
                'Business Loan' => 'business_valid_id',
                'Education Loan' => 'education_valid_id',
                default => null,
            };

            $proofOfIncomeField = match ($lendingType) {
                'Personal Loan' => 'personal_proof_of_income',
                'Emergency Loan' => 'emergency_proof_of_income',
                'Business Loan' => 'business_proof_of_income',
                default => null,
            };

            $referenceNo = 'LN-' . date('YmdHis') . rand(10, 99);

            // ── Compute fees from loan_settings_tbls (Section III. Loan Charges) ──
            $principal = (float) $request->lending_amount;
            $termMonths = (int) filter_var($request->lending_type_term, FILTER_SANITIZE_NUMBER_INT);

            // a. Processing & Collection fee = 2%
            $processingFee = round($principal * ($settings->processing_fee_rate / 100), 2);

            // b. Service and Legal fee = 2%
            $serviceFee = round($principal * ($settings->service_fee_rate / 100), 2);

            // c. Loan Protection Plan = ₱2 per month of term
            $loanProtectionFee = round($settings->loan_protection_fee * $termMonths, 2);

            // e. Retention/CBU = 3% fully paid / 6% not fully paid
            // TODO: wire up real "fully paid subscription" check.
            // Defaulting to the unpaid rate (6%) for everyone until that logic exists.
            $retentionRateApplied = $settings->retention_unpaid_rate;
            $retentionAmount = round($principal * ($retentionRateApplied / 100), 2);

            // Net proceeds = amount actually released to borrower
            $baseNetProceeds = round($principal - $processingFee - $serviceFee - $loanProtectionFee - $retentionAmount, 2);

            // Net Proceeds Adjustment dropdown (no manual amount):
            //  - add    → charges added back, member receives the full loan amount
            //  - deduct / none → charges stay out (base net proceeds)
            $adjustmentType = $request->net_proceeds_adjustment_type === 'add' ? 'add' : null;
            $netProceeds = $adjustmentType === 'add' ? round($principal, 2) : $baseNetProceeds;

            // d. Interest rate = %/mo diminishing balance (equal principal) —
            //    single source of truth: LoanCalculationService
            // In "add fees back" mode the fees become an ADDITIONAL repayment
            // liability folded into each installment. They never touch the
            // interest-bearing balance, so interest is still computed on the
            // principal only. In deduct mode the fees are withheld from the
            // release, so no fee is added to the schedule (feeAmount = 0).
            $totalFees = round($processingFee + $serviceFee + $loanProtectionFee + $retentionAmount, 2);
            $feeToSchedule = $adjustmentType === 'add' ? $totalFees : 0;
            $loanCalc = new \App\Services\LoanCalculationService;
            $schedule = $loanCalc->buildSchedule($principal, (float) $settings->interest_rate, $termMonths, null, $feeToSchedule);

            $totalInterest = $schedule['total_interest'];
            $totalPayment = $schedule['total_payment'];
            $monthlyPayment = $loanCalc->averageMonthlyPayment($schedule);

            $loan = lending_program_tbl::create([
                'user_id' => $memberId,
                'reference_no' => $referenceNo,
                'lending_type' => $lendingType,
                'lending_amount' => $principal,
                'lending_type_term' => $request->lending_type_term,
                'due_date' => now()->timezone('Asia/Manila')->addMonths($termMonths)->format('Y-m-d'), // ← add this

                // Computed fees — keys must match the actual columns
                'processing_fee_rate' => $processingFee,
                'service_fee_rate' => $serviceFee,
                'loan_protection_fee' => $loanProtectionFee,
                'retention_paid_rate' => $retentionRateApplied == $settings->retention_paid_rate ? $retentionAmount : 0,
                'retention_unpaid_rate' => $retentionRateApplied == $settings->retention_unpaid_rate ? $retentionAmount : 0,
                'net_proceeds' => $netProceeds,
                'net_proceeds_adjustment_type' => $adjustmentType,

                'monthly_income' => $request->monthly_income,
                'monthly_payment' => $monthlyPayment,
                'total_payment' => $totalPayment,
                'total_interest' => $totalInterest,

                'purpose_loan' => $request->purpose_loan === 'Others'
                    ? $request->purpose_loan_others
                    : $request->purpose_loan,
                'status' => 'Pending',
                'valid_id' => $validIdField ? $storeFile($validIdField, 'valid_id') : null,
                'proof_of_income' => $proofOfIncomeField ? $storeFile($proofOfIncomeField, 'proof_of_income') : null,
                'proof_of_emergency' => $storeFile('proof_of_emergency', 'proof_of_emergency'),
                'business_permit' => $storeFile('business_permit', 'business_permit'),
                'financial_statement' => $storeFile('financial_statement', 'financial_statement'),
                'school_id' => $storeFile('school_id', 'school_id'),
                'cor' => $storeFile('cor', 'cor'),
            ]);

            // Persist the installment schedule so repayments can be allocated
            // per-installment (diminishing balance) instead of a flat ratio.
            $loanCalc->persistSchedule($loan->id, $schedule);

            AuditLog::log(
                'Member Loan Application',
                "Applied for {$lendingType} loan of ₱{$request->lending_amount} (Ref: {$referenceNo})",
                'loan',
                $loan->id
            );

            return redirect()->route('LoanApplication')
                ->with('ApplySuccess', true)
                ->with('ReferenceNo', $referenceNo)
                ->with('DateFiled', now()->timezone('Asia/Manila')->format('M d, Y · h:i A'))
                ->with('MemberName', trim(Auth::user()->first_name . ' ' . Auth::user()->last_name) ?: Auth::user()->username);

        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function adminApplyOverduePenalties(Request $request, \App\Services\LoanPenaltyService $service)
    {
        $results = $service->applyPenaltiesForAllOverdueLoans(force: (bool) $request->boolean('force'));

        return response()->json([
            'success' => true,
            'message' => 'Applied penalties to ' . count($results) . ' overdue loan(s).',
            'penalties' => $results,
        ]);
    }

    // ─── Repayment ────────────────────────────────────────────────────────────────
    // ─── Repayment ────────────────────────────────────────────────────────────────
    // ─── Repayment ────────────────────────────────────────────────────────────────
    public function storeRepayment(Request $request)
    {
        $rules = [
            'lending_id' => 'required|exists:lending_program_tbls,id',
            'amount_paid' => 'required|numeric|min:1',
            'payment_method' => ['required', 'string', \Illuminate\Validation\Rule::in(\App\Models\PaymentMethod::where('is_active', true)->pluck('method_name')->toArray())],
            'payment_type' => 'nullable|in:monthly,full',
            'gcash_reference_no' => 'nullable|string',
            'installments' => 'nullable|array',
            'installments.*' => 'integer|min:1',
        ];

        if (strtolower($request->payment_method) === 'gcash') {
            $rules['gcash_proof'] = 'required|image|mimes:jpg,jpeg,png|max:5120';
        }

        $request->validate($rules);

        if (strtolower($request->payment_method) === 'gcash') {
            if (!$request->gcash_reference_no) {
                return redirect()->back()->with('error', 'GCash reference number is required for GCash payments.');
            }
            if (strlen($request->gcash_reference_no) !== 13) {
                return redirect()->back()->with('error', 'GCash reference number must be exactly 13 characters.');
            }
            if (
                DB::table('lending_repayments_tbls')->where('gcash_reference_no', $request->gcash_reference_no)->where('status', '!=', 'voided')->exists()
                || DB::table('share_capital_transaction_tbls')->where('gcash_reference_no', $request->gcash_reference_no)->where('status', '!=', 'voided')->exists()
                || DB::table('savings_transaction_tbls')->where('gcash_reference_no', $request->gcash_reference_no)->where('status', '!=', 'voided')->exists()
            ) {
                return redirect()->back()->with('error', 'This reference number has already been used for a transaction.');
            }
        }

        $proofPath = $request->hasFile('gcash_proof')
            ? $request->file('gcash_proof')->store('documents/gcash_proofs', 'public')
            : null;

        $loan = lending_program_tbl::findOrFail($request->lending_id);
        $status = lending_status_tbl::where('lending_id', $request->lending_id)->first();
        $paymentType = $request->get('payment_type', 'monthly');
        $isGcash = strtolower($request->payment_method) === 'gcash';

        // Multi-installment payment (checkbox selection in the Make a Payment modal)
        if ($request->filled('installments') && $status) {
            return $this->storeMultiInstallmentRepayment($request, $loan, $status, $proofPath, $isGcash);
        }

        $totalPayment = (float) ($loan->total_payment ?? $loan->lending_amount);
        $totalPayments = (int) ($status->total_payments ?? 0);
        $monthlyPayment = $totalPayments > 0 ? round($totalPayment / $totalPayments, 2) : (float) $request->amount_paid;

        $loanCalc = new \App\Services\LoanCalculationService;
        $currentInstallment = $loanCalc->currentInstallment($loan->id, true);

        $penaltyNote = null;
        $penaltyAmountForRecord = 0;
        $nextDueDate = null;

        if ($status) {
            $nextDueDate = $status->due_date
                ? \Carbon\Carbon::parse($status->due_date)
                : \Carbon\Carbon::parse($loan->created_at)
                    ->addMonthsNoOverflow(((int) $status->payments_made + 1) * self::PAYMENT_INTERVAL_MONTHS);

            $isOverdue = $nextDueDate->lt(now()->timezone('Asia/Manila'));
            $alreadyPenalized = $status->last_penalty_date
                && \Carbon\Carbon::parse($status->last_penalty_date)->gte($nextDueDate);

            $lateFeeRate = Loan_settings_tbl::getLateFeeRate($loan->lending_type);

            if ($isOverdue && !$alreadyPenalized) {
                // Use the overdue installment's amount for the penalty base,
                // NOT the currentInstallment (which may have skipped ahead
                // past pending payments). This matches how the member view
                // computes PENALTY_PREVIEW.
                $overdueScheduleRow = DB::table('lending_installment_schedules_tbls')
                    ->where('lending_id', $loan->id)
                    ->where('due_date', $nextDueDate->format('Y-m-d'))
                    ->first();
                $penaltyBase = $overdueScheduleRow
                    ? (float) $overdueScheduleRow->amount_due
                    : ($currentInstallment ? (float) $currentInstallment->amount_due : $monthlyPayment);
                $penaltyAmountForRecord = round($penaltyBase * ($lateFeeRate / 100), 2);
                $penaltyNote = '₱' . number_format($penaltyAmountForRecord, 2) . " overdue penalty applied (installment due {$nextDueDate->format('M d, Y')})";
            }
        }

        $combinedNotes = trim(implode(' — ', array_filter([$request->notes, $penaltyNote])));
        $interestRatio = ($loan->total_payment > 0) ? ($loan->total_interest / $loan->total_payment) : 0;

        $memberUser = Auth::user();
        $memberName = trim(($memberUser->first_name ?? '') . ' ' . ($memberUser->last_name ?? '')) ?: 'Member';
        $receiptRef = $request->reference_no ?: 'RCP-' . now()->format('YmdHis');

        // ── SCHEDULE-BASED LOANS ───────────────────────────────────────────────
        if ($currentInstallment) {
            $cashReceived = round((float) $request->amount_paid, 2);
            $installmentNumber = (int) $currentInstallment->payment_number;
            $installmentDueDate = $currentInstallment->due_date;

            if ($paymentType !== 'full') {
                $validation = $loanCalc->validateInstallmentPayment(
                    $loan->id,
                    $cashReceived,
                    (float) $penaltyAmountForRecord,
                    true
                );

                if (!$validation['valid']) {
                    return redirect()->back()->with('error', $validation['message']);
                }

                $installmentAmount = (float) $validation['amount_to_allocate'];
            } else {
                $installmentAmount = $cashReceived;
            }

            // ALL member-submitted payments: create PENDING record only — no balance changes
            $existingCount = lending_repayments_tbl::where('lending_id', $loan->id)
                ->where('payment_number', $installmentNumber)
                ->where('status', '!=', 'voided')
                ->count();

            lending_repayments_tbl::create([
                'lending_id' => $loan->id,
                'user_id' => auth()->id(),
                'payment_number' => $installmentNumber,
                'payment_sequence' => $existingCount + 1,
                'amount_due' => $installmentAmount,
                'amount_paid' => $installmentAmount,
                'late_fee' => $penaltyAmountForRecord,
                'penalty_applied_at' => $penaltyAmountForRecord > 0 ? now()->timezone('Asia/Manila') : null,
                'payment_proof_path' => $proofPath,
                'principal_paid' => 0,
                'interest_paid' => 0,
                'service_fee_paid' => 0,
                'due_date' => $installmentDueDate ?: ($status->due_date ?? now()->format('Y-m-d')),
                'payment_date' => now()->format('Y-m-d'),
                'payment_method' => $request->payment_method,
                'payment_type' => $paymentType,
                'reference_no' => $request->reference_no ?: 'RCP-' . now()->format('YmdHis'),
                'gcash_reference_no' => $isGcash ? $request->gcash_reference_no : null,
                'notes' => $combinedNotes ?: null,
                'recorded_by' => null,
                'status' => 'Pending',
            ]);

            AuditLog::log(
                'Loan Repayment Request',
                "{$request->payment_method} payment of ₱{$request->amount_paid} on loan (ID: {$request->lending_id}), pending verification",
                'loan',
                $request->lending_id
            );

            return redirect()->route('LoanStatus', ['loan_id' => $request->lending_id])->with([
                'success' => 'Payment submitted! Your payment is pending admin verification.',
                'loan_receipt_member' => $memberName,
                'loan_receipt_amount' => $request->amount_paid,
                'loan_receipt_method' => $request->payment_method,
                'loan_receipt_ref' => $receiptRef,
                'loan_receipt_status' => 'Pending',
                'loan_receipt_payment_number' => $installmentNumber,
                'loan_receipt_lending_ref' => $loan->reference_no ?? ('LN-' . $loan->id),
            ]);

            // ── LEGACY LOANS (no schedule rows) ────────────────────────────────────
        } else {
            if ($penaltyAmountForRecord > 0 && $status) {
                $status->penalty_amount = (float) ($status->penalty_amount ?? 0) + $penaltyAmountForRecord;
                $status->remaining_balance = (float) $status->remaining_balance + $penaltyAmountForRecord;
                $status->last_penalty_date = $nextDueDate->format('Y-m-d');
                $status->save();

                AuditLog::log(
                    'Loan Overdue Penalty',
                    "Applied 2% overdue penalty of ₱{$penaltyAmountForRecord} on loan (ID: {$request->lending_id})",
                    'loan',
                    $request->lending_id
                );
            }

            $interestPaid = round($request->amount_paid * $interestRatio, 2);
            $principalPaid = round($request->amount_paid - $interestPaid, 2);

            // ALL member-submitted payments: create PENDING record only — no balance changes
            $existingCount = lending_repayments_tbl::where('lending_id', $request->lending_id)
                ->where('payment_number', $request->payment_number)
                ->where('status', '!=', 'voided')
                ->count();

            lending_repayments_tbl::create([
                'lending_id' => $request->lending_id,
                'user_id' => auth()->id(),
                'payment_number' => $request->payment_number,
                'payment_sequence' => $existingCount + 1,
                'amount_due' => $monthlyPayment,
                'amount_paid' => $request->amount_paid,
                'late_fee' => $penaltyAmountForRecord,
                'penalty_applied_at' => $penaltyAmountForRecord > 0 ? now()->timezone('Asia/Manila') : null,
                'payment_proof_path' => $proofPath,
                'principal_paid' => $principalPaid,
                'interest_paid' => $interestPaid,
                'service_fee_paid' => 0,
                'due_date' => $status->due_date ?? now()->format('Y-m-d'),
                'payment_date' => now()->format('Y-m-d'),
                'payment_method' => $request->payment_method,
                'payment_type' => $paymentType,
                'reference_no' => $request->reference_no ?: 'RCP-' . now()->format('YmdHis'),
                'gcash_reference_no' => $isGcash ? $request->gcash_reference_no : null,
                'notes' => $combinedNotes ?: null,
                'recorded_by' => null,
                'status' => 'Pending',
            ]);

            AuditLog::log(
                'Loan Repayment Request',
                "{$request->payment_method} payment of ₱{$request->amount_paid} on loan (ID: {$request->lending_id}), pending verification",
                'loan',
                $request->lending_id
            );

            return redirect()->route('LoanStatus', ['loan_id' => $request->lending_id])->with([
                'success' => 'Payment submitted! Your payment is pending admin verification.',
                'loan_receipt_member' => $memberName,
                'loan_receipt_amount' => $request->amount_paid,
                'loan_receipt_method' => $request->payment_method,
                'loan_receipt_ref' => $receiptRef,
                'loan_receipt_status' => 'Pending',
                'loan_receipt_payment_number' => $request->payment_number,
                'loan_receipt_lending_ref' => $loan->reference_no ?? ('LN-' . $loan->id),
            ]);
        }
    }

    /**
     * Unpaid installments the member can still pay (not already paid, not already pending),
     * in order, with the late fee each one currently carries.
     */
    private function payableInstallments($loan, $status): \Illuminate\Support\Collection
    {
        if (!$status) {
            return collect();
        }

        $today = now()->timezone('Asia/Manila');
        $lateFeeRate = Loan_settings_tbl::getLateFeeRate($loan->lending_type);
        $totalPayments = (int) $status->total_payments;
        $paymentsMade = (int) $status->payments_made;
        $totalPayment = (float) ($loan->total_payment ?? $loan->lending_amount);
        $monthlyDue = $totalPayments > 0 ? round($totalPayment / $totalPayments, 2) : 0;

        $schedule = DB::table('lending_installment_schedules_tbls')
            ->where('lending_id', $loan->id)
            ->get()
            ->keyBy('payment_number');

        $pending = lending_repayments_tbl::where('lending_id', $loan->id)
            ->where('status', 'Pending')
            ->pluck('payment_number')
            ->map(fn($n) => (int) $n)
            ->all();

        // ← add this
        $paidNumbers = lending_repayments_tbl::where('lending_id', $loan->id)
            ->get()
            ->reject(fn($p) => in_array(strtolower($p->status ?? ''), ['pending', 'voided']))
            ->pluck('payment_number')
            ->map(fn($n) => (int) $n)
            ->unique()
            ->all();

        $nextNo = $paymentsMade + 1;
        $anchor = $status->due_date
            ? \Carbon\Carbon::parse($status->due_date)
            : \Carbon\Carbon::parse($loan->created_at)->addMonthsNoOverflow($nextNo * self::PAYMENT_INTERVAL_MONTHS);


        $rows = collect();

        for ($i = 1; $i <= $totalPayments; $i++) {
            $row = $schedule[$i] ?? null;
            $amount = $row ? (float) $row->amount_due : $monthlyDue;
            $isPaid = $row ? (float) $row->amount_paid >= (float) $row->amount_due : $i <= $paymentsMade;

            if ($isPaid || in_array($i, $pending, true) || in_array($i, $paidNumbers, true)) { // ← added
                continue;
            }

            $due = $this->dueDateFromAnchor($anchor, $i - $nextNo);
            $overdue = $due->lt($today);

            $penalty = 0;
            if ($overdue) {
                $already = $status->last_penalty_date
                    && \Carbon\Carbon::parse($status->last_penalty_date)->gte($due);
                if (!$already) {
                    $penalty = round($amount * ($lateFeeRate / 100), 2);
                }
            }

            $rows->push([
                'number' => $i,
                'date' => $due->format('M d, Y'),
                'due_date' => $due->format('Y-m-d'),
                'amount' => $amount,
                'penalty' => $penalty,
                'overdue' => $overdue,
            ]);
        }

        return $rows->values();
    }

    private function storeMultiInstallmentRepayment(Request $request, $loan, $status, ?string $proofPath, bool $isGcash)
    {
        $payable = $this->payableInstallments($loan, $status);
        $selected = collect($request->input('installments', []))
            ->map(fn($n) => (int) $n)->unique()->sort()->values();

        if ($selected->isEmpty() || $payable->isEmpty()) {
            return redirect()->back()->with('error', 'Please select at least one installment to pay.');
        }

        // Must be consecutive, starting from the earliest unpaid installment.
        $rows = $payable->take($selected->count())->values();
        if ($selected->all() !== $rows->pluck('number')->all()) {
            return redirect()->back()->with('error', 'Installments must be paid in order, starting with the earliest unpaid one.');
        }

        $expectedTotal = round($rows->sum(fn($r) => $r['amount'] + $r['penalty']), 2);
        if (abs(round((float) $request->amount_paid, 2) - $expectedTotal) > 0.01) {
            return redirect()->back()->with('error', 'The amount must be exactly ₱' . number_format($expectedTotal, 2) . ' for the selected installments.');
        }

        $paymentType = $rows->count() === $payable->count() ? 'full' : 'monthly';
        $batchRef = $request->reference_no ?: 'RCP-' . now()->format('YmdHis');

        DB::transaction(function () use ($rows, $loan, $request, $proofPath, $isGcash, $paymentType, $batchRef) {
            foreach ($rows as $row) {
                $existingCount = lending_repayments_tbl::where('lending_id', $loan->id)
                    ->where('payment_number', $row['number'])
                    ->where('status', '!=', 'voided')
                    ->count();

                $penaltyNote = $row['penalty'] > 0
                    ? '₱' . number_format($row['penalty'], 2) . " overdue penalty applied (installment due {$row['date']})"
                    : null;

                lending_repayments_tbl::create([
                    'lending_id' => $loan->id,
                    'user_id' => auth()->id(),
                    'payment_number' => $row['number'],
                    'payment_sequence' => $existingCount + 1,
                    'amount_due' => $row['amount'],
                    'amount_paid' => $row['amount'],
                    'late_fee' => $row['penalty'],
                    'penalty_applied_at' => $row['penalty'] > 0 ? now()->timezone('Asia/Manila') : null,
                    'payment_proof_path' => $proofPath,
                    'principal_paid' => 0,
                    'interest_paid' => 0,
                    'service_fee_paid' => 0,
                    'due_date' => $row['due_date'],
                    'payment_date' => now()->format('Y-m-d'),
                    'payment_method' => $request->payment_method,
                    'payment_type' => $paymentType,
                    'reference_no' => $batchRef,
                    'gcash_reference_no' => $isGcash ? $request->gcash_reference_no : null,
                    'notes' => trim(implode(' — ', array_filter([$request->notes, $penaltyNote]))) ?: null,
                    'recorded_by' => null,
                    'status' => 'Pending',
                ]);
            }
        });

        $numbers = $rows->pluck('number');
        $numberLabel = $numbers->count() > 1 ? $numbers->first() . '–' . $numbers->last() : (string) $numbers->first();

        AuditLog::log(
            'Loan Repayment Request',
            "{$request->payment_method} payment of ₱{$expectedTotal} for installment(s) #{$numberLabel} on loan (ID: {$loan->id}), pending verification",
            'loan',
            $loan->id
        );

        $member = Auth::user();

        return redirect()->route('LoanStatus', ['loan_id' => $loan->id])->with([
            'success' => 'Payment submitted! Your payment is pending admin verification.',
            'loan_receipt_member' => trim(($member->first_name ?? '') . ' ' . ($member->last_name ?? '')) ?: 'Member',
            'loan_receipt_amount' => $expectedTotal,
            'loan_receipt_method' => $request->payment_method,
            'loan_receipt_ref' => $batchRef,
            'loan_receipt_status' => 'Pending',
            'loan_receipt_payment_number' => $numberLabel,
            'loan_receipt_lending_ref' => $loan->reference_no ?? ('LN-' . $loan->id),
        ]);
    }

    // ─── Loan Status page ─────────────────────────────────────────────────────────
    // ─── Loan Status page ─────────────────────────────────────────────────────────
    public function loanStatus(Request $request)
    {
        // $this->autoProcessOverdueLoans();
        $memberId = auth()->id();
        $username = Auth::check() ? Auth::user()->username : null;
        $email = Auth::check() ? Auth::user()->email : null;

        $typeMap = [
            'Personal Lending' => 'Personal Loan',
            'Emergency Lending' => 'Emergency Loan',
            'Business Lending' => 'Business Loan',
            'Education Lending' => 'Education Loan',
        ];

        $today = now()->timezone('Asia/Manila')->toDateString();

        // Show Approved AND Completed loans so members can browse everything,
        // including fully paid loans, from this one grid.
        $loans = DB::table('lending_program_tbls as l')
            ->leftJoin('lending_status_tbls as s', 's.lending_id', '=', 'l.id')
            ->where('l.user_id', $memberId)
            ->whereIn('l.status', ['Approved', 'Completed'])
            ->orderBy('l.created_at', 'desc')
            ->select('l.*', 's.remaining_balance', 's.payments_made', 's.total_payments', 's.due_date')
            ->get()
            ->map(function ($loan) use ($typeMap, $today) {
                $loan->display_type = $typeMap[$loan->lending_type] ?? $loan->lending_type;

                $loan->card_status = $this->resolveLoanStatusLabel($loan, $loan);

                $totalPayments = (int) ($loan->total_payments ?? 0);
                $paymentsMade = (int) ($loan->payments_made ?? 0);
                $loan->progress_percent = $totalPayments > 0
                    ? min(100, round(($paymentsMade / $totalPayments) * 100))
                    : 0;

                $totals = $this->loanChargeTotals($loan);
                $loan->total_charges = $totals['total_charges'];
                $loan->total_payable = $totals['total_payable'];

                // Per-installment now reflects the charge-inclusive payable
                $loan->monthly_payment = $totalPayments > 0
                    ? round($loan->total_payable / $totalPayments, 2)
                    : 0;

                return $loan;
            });

        // No auto-select — leave $selectedLoan null unless the URL explicitly
        // carries ?loan_id=, so the grid shows by default.
        $selectedId = $request->get('loan_id');

        $selectedLoan = $selectedId
            ? lending_program_tbl::where('id', $selectedId)->where('user_id', $memberId)->first()
            : null;

        if ($selectedLoan) {
            $selectedLoan->display_type = $typeMap[$selectedLoan->lending_type] ?? $selectedLoan->lending_type;
        }

        $lendingStatus = $selectedLoan
            ? lending_status_tbl::where('lending_id', $selectedLoan->id)->first()
            : null;

        if ($selectedLoan && !$lendingStatus && $selectedLoan->status === 'Approved') {
            $termMonths = (int) filter_var($selectedLoan->lending_type_term, FILTER_SANITIZE_NUMBER_INT);
            $interestRate = \App\Models\Loan_settings_tbl::getRate($selectedLoan->lending_type);

            $lendingStatus = lending_status_tbl::create([
                'lending_id' => $selectedLoan->id,
                'user_id' => $selectedLoan->user_id,
                'remaining_balance' => $selectedLoan->total_payment,
                'total_paid' => 0,
                'payments_made' => 0,
                'total_payments' => $termMonths,
                'interest_rate' => $interestRate,
                'due_date' => \Carbon\Carbon::parse($selectedLoan->created_at)
                    ->addMonthsNoOverflow(self::PAYMENT_INTERVAL_MONTHS)->format('Y-m-d'),
                'status' => 'Active',
            ]);

            AuditLog::log(
                'Loan Status Initialized',
                "Initialized lending status for loan #{$selectedLoan->id} (auto-created on status view)",
                'loan_status',
                $lendingStatus->id
            );
        }

        $paymentHistory = $selectedLoan
            ? lending_repayments_tbl::where('lending_id', $selectedLoan->id)
                ->orderBy('payment_date', 'desc')->get()
            : collect();

        // Map installment # → the actual late fee charged on that payment,
        // pulled straight from the real repayment record (not the aggregate
        // on lending_status_tbls) so the Payment Schedule can show exactly
        // which installment the penalty was applied to.
        $penaltyByInstallment = $paymentHistory
            ->filter(fn($p) => ($p->late_fee ?? 0) > 0)
            ->keyBy('payment_number');

        // Installment numbers that already have a pending (non-voided) payment.
        // Used below to skip them when determining the "next" payment the member
        // should submit, so the form advances to the next installment after a
        // submission instead of showing the same one again.
        $pendingInstallmentNumbers = $selectedLoan
            ? $paymentHistory
                ->where('status', 'Pending')
                ->pluck('payment_number')
                ->map(fn($n) => (int) $n)
                ->unique()
                ->values()
                ->toArray()
            : [];

        $pendingCount = count($pendingInstallmentNumbers);

        $voidedInstallmentNumbers = $selectedLoan
            ? $paymentHistory
                ->where('status', 'voided')
                ->pluck('payment_number')
                ->map(fn($n) => (int) $n)
                ->unique()
                ->values()
                ->toArray()
            : [];

        $paidInstallmentNumbers = $selectedLoan
            ? $paymentHistory
                ->reject(fn($p) => in_array(strtolower($p->status ?? ''), ['pending', 'voided']))
                ->pluck('payment_number')
                ->map(fn($n) => (int) $n)
                ->unique()
                ->values()
                ->toArray()
            : [];

        // ── Build computed hero/breakdown data ──────────────────────────────────
        $paymentSchedule = collect();
        $progressPercent = 0;
        $remainingPrincipal = 0;
        $monthlyDue = 0;
        $currentDueAmount = 0;
        $hasSchedule = false;

        $nextDueDate = null;          // earliest unpaid installment overall (may be overdue)
        // — drives status pill + penalty logic only.
        $displayNextDueDate = null;   // earliest unpaid installment that is NOT overdue —
        // this is what the "Next Due" hero box shows. "Next Due"
        // means the next thing coming up, so it should never
        // silently become an already-missed date.
        $daysAway = null;
        $displayDaysAway = null;

        $overdueDate = null;             // earliest unpaid installment that IS overdue, if any.
        $overdueDaysCount = null;        // how many days overdue (positive int).
        $overdueInstallmentNumber = null; // which installment # is overdue.
        $currentOverduePenalty = 0;      // penalty tied SPECIFICALLY to the overdue
        // installment above — 0 whenever nothing is
        // actually overdue right now. Used for the
        // repayment modal prefill so it never adds a
        // stale historical penalty to an Upcoming
        // installment's payment. Deliberately separate
        // from $penaltyAmount below, which is a lifetime
        // running total and should NOT be used for that.

        $fullBalanceRemaining = 0;
        $monthsRemaining = 0;
        $serviceFee = 0;
        $interestRate = 0;
        $totalInterest = 0;
        $processingFee = 0;
        $loanProtectionFee = 0;
        $retentionFee = 0;
        $netProceeds = 0;
        $penaltyAmount = 0;
        $totalCharges = 0;
        $totalPayable = 0;
        $totalChargesWithInterest = 0;
        $loanStatusLabel = 'Active';
        $lateFeeRate = 2.00;
        $nextPaymentNumber = ($lendingStatus->payments_made ?? 0) + 1;

        if ($selectedLoan && $lendingStatus) {
            $principal = (float) $selectedLoan->lending_amount;
            $totalPayments = (int) $lendingStatus->total_payments;
            $paymentsMade = (int) $lendingStatus->payments_made;
            $totalPayment = (float) ($selectedLoan->total_payment ?? $principal);
            $monthlyDue = $totalPayments > 0 ? round($totalPayment / $totalPayments, 2) : 0;

            // Dynamic late-fee rate (%) from Finance settings (2.00 fallback).
            $lateFeeRate = Loan_settings_tbl::getLateFeeRate($selectedLoan->lending_type);

            // Schedule-based loans: the per-installment plan is authoritative.
            // Legacy loans have no schedule rows and fall back to flat $monthlyDue.
            $scheduleRows = DB::table('lending_installment_schedules_tbls')
                ->where('lending_id', $selectedLoan->id)
                ->orderBy('payment_number')
                ->get();
            $scheduleByNumber = $scheduleRows->keyBy('payment_number');
            $hasSchedule = $scheduleRows->isNotEmpty();

            // Amount due for the CURRENT unpaid installment (repayment prefill).
            // Skip installments that already have a pending payment so the form
            // advances to the next installment after a submission.
            $currentDueAmount = $monthlyDue;
            $currentInstallmentRow = $scheduleRows->first(
                fn($row) => (float) $row->amount_paid < (float) $row->amount_due
                    && !in_array((int) $row->payment_number, $pendingInstallmentNumbers)
                    && !in_array((int) $row->payment_number, $paidInstallmentNumbers)
            );
            if ($currentInstallmentRow) {
                $currentDueAmount = (float) $currentInstallmentRow->amount_due;
            }

            // Next payment number the member should submit, accounting for
            // already-pending installments so the hidden form input is correct.
            $nextPaymentNumber = $currentInstallmentRow
                ? (int) $currentInstallmentRow->payment_number
                : (($lendingStatus->payments_made ?? 0) + 1);

            $progressPercent = $totalPayments > 0
                ? min(100, round(($paymentsMade / $totalPayments) * 100, 2))
                : 0;

            $totalInterest = (float) ($selectedLoan->total_interest ?? 0);
            $interestRate = (float) ($lendingStatus->interest_rate ?? 0);

            $processingFee = (float) ($selectedLoan->processing_fee_rate ?? 0);
            $serviceFee = (float) ($selectedLoan->service_fee_rate ?? 0);
            $loanProtectionFee = (float) ($selectedLoan->loan_protection_fee ?? 0);
            $retentionFee = (float) ($selectedLoan->retention_paid_rate ?? 0)
                + (float) ($selectedLoan->retention_unpaid_rate ?? 0);
            $netProceeds = (float) ($selectedLoan->net_proceeds ?? 0);
            $penaltyAmount = (float) ($lendingStatus->penalty_amount ?? 0);

            $chargeTotals = $this->loanChargeTotals($selectedLoan);
            $totalCharges = $chargeTotals['total_charges'];
            $totalPayable = $chargeTotals['total_payable'];
            $totalChargesWithInterest = round($totalCharges + $totalInterest, 2);

            $remainingBalance = (float) $lendingStatus->remaining_balance;
            if ($totalPayment > 0 && $remainingBalance > 0) {
                $remainingPrincipal = round(($remainingBalance / $totalPayment) * $principal, 2);
            } else {
                $remainingPrincipal = 0;
            }

            $fullBalanceRemaining = $remainingBalance;
            $monthsRemaining = max(0, $totalPayments - $paymentsMade);

            // Build amortization / payment schedule, anchored on the authoritative
            // due_date stored in lending_status_tbls.
            $startDate = \Carbon\Carbon::parse($selectedLoan->created_at);
            $today = now()->timezone('Asia/Manila');

            $nextInstallmentNumber = $paymentsMade + 1;
            $anchorDueDate = $lendingStatus->due_date
                ? \Carbon\Carbon::parse($lendingStatus->due_date)
                : $startDate->copy()->addMonthsNoOverflow($nextInstallmentNumber * self::PAYMENT_INTERVAL_MONTHS);

            for ($i = 1; $i <= $totalPayments; $i++) {
                $dueDateForRow = $this->dueDateFromAnchor($anchorDueDate, $i - $nextInstallmentNumber);

                $scheduleRow = $scheduleByNumber[$i] ?? null;
                $installmentAmount = $scheduleRow ? (float) $scheduleRow->amount_due : $monthlyDue;

                $isPaid = in_array($i, $paidInstallmentNumbers, true)   // ← added, checked first
                    || ($scheduleRow
                        ? (float) $scheduleRow->amount_paid >= (float) $scheduleRow->amount_due
                        : ($i <= $paymentsMade));

                $isOverdue = !$isPaid && $dueDateForRow->lt($today);
                $isNext = !$isPaid && !$nextDueDate;

                // Actual charged penalty (from a real repayment record), if any.
                $rowPenalty = $penaltyByInstallment[$i]->late_fee ?? 0;

                // If this row is overdue but hasn't actually been charged yet (no
                // repayment record), fall back to the live 2% preview — same math
                // used for $currentOverduePenalty — so the member sees the real
                // amount they'll owe, not just the base installment.
                if ($isOverdue && $rowPenalty == 0) {
                    $alreadyPenalizedForRow = $lendingStatus->last_penalty_date
                        && \Carbon\Carbon::parse($lendingStatus->last_penalty_date)->gte($dueDateForRow);

                    if (!$alreadyPenalizedForRow) {
                        $rowPenalty = round($installmentAmount * ($lateFeeRate / 100), 2);
                    }
                }

                $isPending = !$isPaid && in_array($i, $pendingInstallmentNumbers, true);
                $isVoided = !$isPaid && !$isPending && in_array($i, $voidedInstallmentNumbers, true);

                $paymentSchedule->push([
                    'number' => $i,
                    'date' => $dueDateForRow->format('M d, Y'),
                    'amount' => $installmentAmount,
                    'paid' => $isPaid,
                    'pending' => $isPending,
                    'voided' => $isVoided,
                    'overdue' => $isOverdue,
                    'is_next' => $isNext,
                    'penalty' => $rowPenalty,
                ]);

                // Earliest unpaid installment overall (may be overdue) — drives
                // the hero status pill + penalty logic. NOT what "Next Due" shows.
                if (!$isPaid && !$nextDueDate) {
                    $nextDueDate = $dueDateForRow;
                }

                // Earliest unpaid installment that is NOT overdue — this is what
                // the "Next Due" hero box actually displays to the member.
                if (!$isPaid && !$isOverdue && !$displayNextDueDate) {
                    $displayNextDueDate = $dueDateForRow;
                }

                // Earliest unpaid installment that IS overdue — surfaced as its
                // own separate note, so it never gets confused with "Next Due".
                if ($isOverdue && !$overdueDate) {
                    $overdueDate = $dueDateForRow;
                    $overdueInstallmentNumber = $i;
                    // Compare start-of-day to start-of-day so a fractional
                    // time-of-day on $today (from now()) doesn't leak into the
                    // count as a decimal, e.g. "2.5249774647801 days overdue".
                    $overdueDaysCount = (int) $dueDateForRow->copy()->startOfDay()
                        ->diffInDays($today->copy()->startOfDay());

                    // Only count this as an outstanding penalty if it hasn't
                    // already been applied for THIS specific due date — this
                    // is what makes $currentOverduePenalty different from the
                    // lifetime $penaltyAmount total below.
                    $alreadyPenalizedForThisOne = $lendingStatus->last_penalty_date
                        && \Carbon\Carbon::parse($lendingStatus->last_penalty_date)->gte($dueDateForRow);

                    if (!$alreadyPenalizedForThisOne) {
                        $currentOverduePenalty = round($installmentAmount * ($lateFeeRate / 100), 2);
                    }
                }
            }

            if ($nextDueDate) {
                $daysAway = number_format($today->diffInDays($nextDueDate, false));
            }

            if ($displayNextDueDate) {
                $displayDaysAway = number_format($today->diffInDays($displayNextDueDate, false));
            }
            // If everything unpaid is overdue (no future installment yet),
            // $displayNextDueDate stays null and the box falls back to "—".

            // Real hero status: Completed / Overdue / Active
            $loanStatusLabel = $this->resolveLoanStatusLabel($selectedLoan, $lendingStatus);

            // ── Live penalty preview ──────────────────────────────────────────
            // penalty_amount in the DB only gets written once a payment is
            // actually submitted (see storeRepayment()). So a loan can be
            // visibly overdue here while penalty_amount is still 0. Show what
            // the 2% penalty WOULD be right now, without persisting anything —
            // the real charge still only gets written when the member pays.
            // This still keys off $nextDueDate (the actual overdue installment),
            // NOT $displayNextDueDate, so the amount always matches whichever
            // installment is really overdue — independent of what "Next Due" shows.
            if ($nextDueDate && $nextDueDate->lt($today)) {
                $alreadyPenalizedForThis = $lendingStatus->last_penalty_date
                    && \Carbon\Carbon::parse($lendingStatus->last_penalty_date)->gte($nextDueDate);

                if (!$alreadyPenalizedForThis) {
                    $overdueScheduleRow = $scheduleByNumber[$overdueInstallmentNumber] ?? null;
                    $penaltyAmount += round(($overdueScheduleRow ? (float) $overdueScheduleRow->amount_due : $monthlyDue) * ($lateFeeRate / 100), 2);
                }
            }
        }

        $payableInstallments = ($selectedLoan && $lendingStatus)
            ? $this->payableInstallments($selectedLoan, $lendingStatus)
            : collect();

        // The QR the admin uploaded in Settings → Payment Methods Management
        $gcashPaymentMethod = \App\Models\PaymentMethod::where('method_name', 'GCash')
            ->where('is_active', true)
            ->first();

        $paymentMethods = \App\Models\PaymentMethod::where('is_active', true)->orderBy('id')->get();

        return view('members_components.loan_status', array_merge(
            ['username' => $username, 'email' => $email],
            compact(
                'loans',
                'selectedLoan',
                'lendingStatus',
                'paymentHistory',
                'paymentSchedule',
                'progressPercent',
                'remainingPrincipal',
                'monthlyDue',
                'currentDueAmount',
                'nextDueDate',
                'daysAway',
                'displayNextDueDate',
                'displayDaysAway',
                'overdueDate',
                'overdueDaysCount',
                'overdueInstallmentNumber',
                'currentOverduePenalty',
                'fullBalanceRemaining',
                'monthsRemaining',
                'serviceFee',
                'interestRate',
                'totalInterest',
                'processingFee',
                'loanProtectionFee',
                'retentionFee',
                'netProceeds',
                'penaltyAmount',
                'loanStatusLabel',
                'gcashPaymentMethod',
                'paymentMethods',
                'hasSchedule',
                'nextPaymentNumber',
                'penaltyAmount',
                'totalCharges',
                'totalPayable',
                'loanStatusLabel',
                'payableInstallments',
                'pendingCount',
                'totalChargesWithInterest'
            )
        ));
    }
}
