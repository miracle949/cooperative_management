<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Single source of truth for loan interest + installment schedule math.
 *
 * Method: diminishing balance with equal principal. `interest_rate` in
 * loan_settings_tbls is a MONTHLY percentage.
 *
 * Rounding rule: every installment's principal is rounded to 2dp and the
 * FINAL installment absorbs any cent difference, so the sum of all
 * principal_due equals the original principal exactly. Interest is computed
 * each period on the rounded running balance and rounded to 2dp — it is never
 * used to compensate principal rounding.
 */
class LoanCalculationService
{
    public const PAYMENT_INTERVAL_DAYS = 5;

    /**
     * Build the full installment schedule for a loan.
     *
     * @return array{
     *     installments: array<int, array{number:int, due_date:string, principal_due:float, interest_due:float, amount_due:float, balance_after:float}>,
     *     total_interest: float,
     *     total_payment: float,
     *     monthly_principal: float
     * }
     */
    public function buildSchedule(float $principal, float $monthlyRatePercent, int $termMonths, ?Carbon $startDate = null, float $feeAmount = 0.0): array
    {
        $principal = round($principal, 2);
        $termMonths = max(1, $termMonths);
        $rate = $monthlyRatePercent / 100;
        $startDate = $startDate ? $startDate->copy() : Carbon::now()->timezone('Asia/Manila');
        $feeAmount = round($feeAmount, 2);

        // Equal principal per installment; the final installment absorbs rounding.
        $basePrincipalDue = round($principal / $termMonths, 2);
        $lastPrincipalDue = round($principal - ($basePrincipalDue * ($termMonths - 1)), 2);

        // Fees are an ADDITIONAL repayment liability only — they never touch the
        // interest-bearing balance. Spread evenly; the final installment absorbs
        // rounding so the sum of all fee_due equals the total fee amount.
        $baseFeeDue = round($feeAmount / $termMonths, 2);
        $lastFeeDue = round($feeAmount - ($baseFeeDue * ($termMonths - 1)), 2);

        $installments = [];
        $balance = $principal;
        $totalInterest = 0.0;
        $totalFee = 0.0;

        for ($i = 1; $i <= $termMonths; $i++) {
            $principalDue = $i === $termMonths ? $lastPrincipalDue : $basePrincipalDue;
            $interestDue = round($balance * $rate, 2);
            $feeDue = $i === $termMonths ? $lastFeeDue : $baseFeeDue;
            $amountDue = round($principalDue + $interestDue + $feeDue, 2);
            $balance = round($balance - $principalDue, 2);

            $totalInterest += $interestDue;
            $totalFee += $feeDue;

            $installments[] = [
                'number' => $i,
                'due_date' => $startDate->copy()->addDays($i * self::PAYMENT_INTERVAL_DAYS)->format('Y-m-d'),
                'principal_due' => $principalDue,
                'interest_due' => $interestDue,
                'fee_due' => $feeDue,
                'amount_due' => $amountDue,
                'balance_after' => $balance,
            ];
        }

        $totalInterest = round($totalInterest, 2);
        $totalFee = round($totalFee, 2);

        return [
            'installments' => $installments,
            'total_interest' => $totalInterest,
            'total_fee' => $totalFee,
            'total_payment' => round($principal + $totalInterest + $totalFee, 2),
            'monthly_principal' => $basePrincipalDue,
        ];
    }

    /**
     * Persist schedule rows for a loan. Per-installment paid counters are the
     * authority; `is_paid` is only a cache flag (true once outstanding = 0).
     */
    public function persistSchedule(int $lendingId, array $schedule): void
    {
        $now = now();

        $rows = array_map(function (array $installment) use ($lendingId, $now) {
            return [
                'lending_id' => $lendingId,
                'payment_number' => $installment['number'],
                'due_date' => $installment['due_date'],
                'principal_due' => $installment['principal_due'],
                'interest_due' => $installment['interest_due'],
                'fee_due' => $installment['fee_due'] ?? 0,
                'amount_due' => $installment['amount_due'],
                'balance_after' => $installment['balance_after'],
                'principal_paid' => 0,
                'interest_paid' => 0,
                'fee_paid' => 0,
                'amount_paid' => 0,
                'is_paid' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }, $schedule['installments']);

        DB::table('lending_installment_schedules_tbls')->insert($rows);
    }

    /**
     * The first installment whose outstanding has not yet reached zero, or null.
     */
    public function currentInstallment(int $lendingId, bool $skipPending = false): ?object
    {
        $rows = DB::table('lending_installment_schedules_tbls')
            ->where('lending_id', $lendingId)
            ->orderBy('payment_number')
            ->get();

        if ($skipPending) {
            $pendingNumbers = DB::table('lending_repayments_tbls')
                ->where('lending_id', $lendingId)
                ->where('status', 'Pending')
                ->pluck('payment_number')
                ->unique()
                ->values()
                ->toArray();

            $rows = $rows->filter(
                fn ($row) => ! in_array((int) $row->payment_number, $pendingNumbers)
            )->values();
        }

        return $rows->first(fn ($row) => (float) $row->amount_paid < (float) $row->amount_due);
    }

    /**
     * Validate a monthly repayment against the CURRENT unpaid installment.
     *
     * Business rule: for scheduled loans partial payments are NOT allowed and
     * excess payments must NOT be rolled into other installments. The required
     * total is the current installment amount plus any applicable late fee
     * (which is booked separately and never allocated into the schedule).
     *
     * Loans without schedule rows (legacy) are NOT enforced here — they keep
     * their existing flat-ratio repayment behavior.
     *
     * @return array{valid:bool, installment:?object, amount_to_allocate:float, expected_total:float, message:?string}
     */
    public function validateInstallmentPayment(int $lendingId, float $amount, float $penalty = 0.0, bool $skipPending = false): array
    {
        $installment = $this->currentInstallment($lendingId, $skipPending);

        if (! $installment) {
            return [
                'valid' => true,
                'installment' => null,
                'amount_to_allocate' => 0.0,
                'expected_total' => 0.0,
                'message' => null,
            ];
        }

        $due = (float) $installment->amount_due;
        $expectedTotal = round($due + $penalty, 2);

        if (abs($amount - $expectedTotal) <= 0.005) {
            return [
                'valid' => true,
                'installment' => $installment,
                'amount_to_allocate' => $due,
                'expected_total' => $expectedTotal,
                'message' => null,
            ];
        }

        $label = '₱'.number_format($expectedTotal, 2);
        if ($penalty > 0) {
            $label .= ' (includes ₱'.number_format($penalty, 2).' late fee)';
        }

        $message = $amount < $expectedTotal
            ? "Partial payments are not allowed. Please pay the full current installment of {$label}."
            : "Payment exceeds the current installment. Please enter exactly {$label}.";

        return [
            'valid' => false,
            'installment' => $installment,
            'amount_to_allocate' => $due,
            'expected_total' => $expectedTotal,
            'message' => $message,
        ];
    }

    /**
     * Allocate a payment across the unpaid schedule, interest-first.
     *
     * Interest of the current installment is satisfied before any principal;
     * any excess rolls forward to the next unpaid installment. An installment
     * only becomes fully paid when its outstanding reaches zero.
     *
     * Callers must run inside the same DB transaction as their own writes.
     *
     * @return array{allocated:bool, principal_paid?:float, interest_paid?:float, fee_paid?:float, amount_paid?:float, installments_settled?:int, fully_paid_now?:bool}
     */
    public function allocatePayment(int $lendingId, float $amount): array
    {
        $installments = DB::table('lending_installment_schedules_tbls')
            ->where('lending_id', $lendingId)
            ->orderBy('payment_number')
            ->get();

        if ($installments->isEmpty()) {
            return ['allocated' => false];
        }

        $totalCount = $installments->count();
        $settledBefore = $installments->filter(
            fn ($row) => (float) $row->amount_paid >= (float) $row->amount_due
        )->count();

        $remaining = round($amount, 2);
        $totalPrincipal = 0.0;
        $totalInterest = 0.0;
        $totalFee = 0.0;

        foreach ($installments as $row) {
            if ($remaining <= 0) {
                break;
            }

            $amountDue = (float) $row->amount_due;
            $amountPaid = (float) $row->amount_paid;

            if ($amountPaid >= $amountDue) {
                continue;
            }

            $interestPaid = (float) $row->interest_paid;
            $principalPaid = (float) $row->principal_paid;
            $feePaid = (float) $row->fee_paid;
            $interestOutstanding = round((float) $row->interest_due - $interestPaid, 2);
            $feeOutstanding = round((float) $row->fee_due - $feePaid, 2);
            $principalOutstanding = round((float) $row->principal_due - $principalPaid, 2);

            // Interest first…
            $interestApplied = round(min($remaining, $interestOutstanding), 2);
            $interestPaidNew = round($interestPaid + $interestApplied, 2);
            $remaining = round($remaining - $interestApplied, 2);

            // …then fee (additional repayment liability, separate from interest)…
            $feeApplied = 0.0;
            $feePaidNew = $feePaid;
            if ($remaining > 0 && $feeOutstanding > 0) {
                $feeApplied = round(min($remaining, $feeOutstanding), 2);
                $feePaidNew = round($feePaid + $feeApplied, 2);
                $remaining = round($remaining - $feeApplied, 2);
            }

            // …then principal.
            $principalApplied = 0.0;
            $principalPaidNew = $principalPaid;
            if ($remaining > 0) {
                $principalApplied = round(min($remaining, $principalOutstanding), 2);
                $principalPaidNew = round($principalPaid + $principalApplied, 2);
                $remaining = round($remaining - $principalApplied, 2);
            }

            $totalInterest += $interestApplied;
            $totalFee += $feeApplied;
            $totalPrincipal += $principalApplied;

            $amountPaidNew = round($interestPaidNew + $feePaidNew + $principalPaidNew, 2);

            DB::table('lending_installment_schedules_tbls')
                ->where('id', $row->id)
                ->update([
                    'interest_paid' => $interestPaidNew,
                    'fee_paid' => $feePaidNew,
                    'principal_paid' => $principalPaidNew,
                    'amount_paid' => $amountPaidNew,
                    'is_paid' => $amountPaidNew >= $amountDue,
                    'updated_at' => now(),
                ]);
        }

        $settledAfter = DB::table('lending_installment_schedules_tbls')
            ->where('lending_id', $lendingId)
            ->orderBy('payment_number')
            ->get()
            ->filter(fn ($row) => (float) $row->amount_paid >= (float) $row->amount_due)
            ->count();

        return [
            'allocated' => true,
            'principal_paid' => round($totalPrincipal, 2),
            'interest_paid' => round($totalInterest, 2),
            'fee_paid' => round($totalFee, 2),
            'amount_paid' => round($totalPrincipal + $totalInterest + $totalFee, 2),
            'installments_settled' => $settledAfter - $settledBefore,
            'fully_paid_now' => $settledAfter === $totalCount,
        ];
    }

    /**
     * Average monthly payment for display/compat (total payment ÷ term).
     */
    public function averageMonthlyPayment(array $schedule): float
    {
        $term = count($schedule['installments']);

        return $term > 0 ? round($schedule['total_payment'] / $term, 2) : 0;
    }
}
