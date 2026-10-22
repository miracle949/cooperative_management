<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\lending_status_tbl;
use App\Models\savings_account_tbl;
use App\Models\savings_transaction_tbl;
use Illuminate\Support\Facades\DB;

class LoanPenaltyService
{
    private const PENALTY_RATE = 0.02; // 2% of the monthly amortization

    /**
     * Scan every overdue Approved loan and deduct a 2% penalty from savings.
     */
    public function applyPenaltiesForAllOverdueLoans(bool $force = false): array
    {
        $today = now()->timezone('Asia/Manila')->toDateString();

        $overdueStatuses = lending_status_tbl::where('status', 'Active')
            ->where('remaining_balance', '>', 0)
            ->where('due_date', '<', $today)
            ->get();

        $results = [];

        foreach ($overdueStatuses as $status) {
            if (! $force && $status->last_penalty_date === $today) {
                continue; // already penalized today, skip
            }

            $result = $this->applyPenalty($status, $today);
            if ($result) {
                $results[] = $result;
            }
        }

        return $results;
    }

    private function applyPenalty(lending_status_tbl $status, string $today): ?array
    {
        $loan = DB::table('lending_program_tbls')->where('id', $status->lending_id)->first();
        if (! $loan) {
            return null;
        }

        // Amortization = the loan's regular monthly payment amount
        $totalPayments = (int) ($status->total_payments ?? 0);
        $totalPayment = (float) ($loan->total_payment ?? $loan->lending_amount);
        $monthlyAmortization = $totalPayments > 0
            ? round($totalPayment / $totalPayments, 2)
            : 0;

        if ($monthlyAmortization <= 0) {
            return null;
        }

        $penaltyAmount = round($monthlyAmortization * self::PENALTY_RATE, 2);
        if ($penaltyAmount <= 0) {
            return null;
        }

        $savingsAccount = savings_account_tbl::where('user_id', $status->user_id)->first();
        if (! $savingsAccount) {
            return null; // nothing to deduct from
        }

        // Don't let savings go negative — deduct whatever's available, up to the full penalty
        $deducted = min($penaltyAmount, (float) $savingsAccount->balance);
        if ($deducted <= 0) {
            return null;
        }

        $newBalance = round($savingsAccount->balance - $deducted, 2);
        $referenceNo = 'PEN-'.strtoupper(bin2hex(random_bytes(3))).'-'.now()->format('Ymd');

        DB::transaction(function () use ($savingsAccount, $newBalance, $deducted, $referenceNo, $status, $today, $loan) {
            $savingsAccount->update(['balance' => $newBalance]);

            savings_transaction_tbl::create([
                'savings_account_id' => $savingsAccount->id,
                'type' => 'withdrawal',
                'amount' => $deducted,
                'payment_method' => 'Internal Transfer',
                'balance_after' => $newBalance,
                'note' => "Overdue loan penalty (2% of amortization) — Loan Ref: {$loan->reference_no}",
                'reference_no' => $referenceNo,
                'transaction_date' => $today,
                'status' => 'Completed',
            ]);

            $status->update([
                'penalty_amount' => (float) ($status->penalty_amount ?? 0) + $deducted,
                'last_penalty_date' => $today,
            ]);
        });

        AuditLog::log(
            'Overdue Loan Penalty Applied',
            "Deducted ₱{$deducted} (2% of amortization) from savings — Loan Ref: {$loan->reference_no}",
            'loan_penalty',
            $status->lending_id
        );

        return [
            'lending_id' => $status->lending_id,
            'user_id' => $status->user_id,
            'penalty_amount' => $deducted,
            'reference_no' => $referenceNo,
        ];
    }
}
