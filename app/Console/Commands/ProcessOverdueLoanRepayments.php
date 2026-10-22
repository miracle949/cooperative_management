<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\lending_program_tbl;
use App\Models\lending_repayments_tbl;
use App\Models\lending_status_tbl;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProcessOverdueLoanRepayments extends Command
{
    protected $signature = 'loans:process-overdue';

    protected $description = 'Auto-deduct overdue loan installments from members\' savings balance';

    public function handle()
    {
        $today = now()->timezone('Asia/Manila')->toDateString();

        $overdueStatuses = lending_status_tbl::where('status', 'Active')
            ->where('remaining_balance', '>', 0)
            ->whereDate('due_date', '<', $today)
            ->get();

        $this->info("Found {$overdueStatuses->count()} overdue installment(s).");

        foreach ($overdueStatuses as $status) {
            $loan = lending_program_tbl::find($status->lending_id);
            if (! $loan || $loan->status !== 'Approved') {
                continue;
            }

            $totalPayment = (float) ($loan->total_payment ?? $loan->lending_amount);
            $totalPayments = (int) $status->total_payments;
            $monthlyPayment = $totalPayments > 0 ? round($totalPayment / $totalPayments, 2) : 0;

            // Scheduled loans deduct the exact CURRENT installment (full-installment
            // rule); legacy loans keep the flat average. Penalties are not part of
            // the auto-deduction (unchanged behavior).
            $loanCalc = new \App\Services\LoanCalculationService;
            $currentInstallment = $loanCalc->currentInstallment($loan->id);
            $installmentAmount = $currentInstallment
                ? (float) $currentInstallment->amount_due
                : $monthlyPayment;

            if ($installmentAmount <= 0) {
                continue;
            }

            $savings = DB::table('savings_account_tbls')->where('user_id', $status->user_id)->first();
            $savingsBalance = (float) ($savings->balance ?? 0);

            if (! $savings || $savingsBalance < $installmentAmount) {
                $this->warn("Loan #{$loan->id}: insufficient savings (₱{$savingsBalance}) to cover ₱{$installmentAmount}. Skipped.");

                continue;
            }

            DB::transaction(function () use ($loan, $status, $loanCalc, $currentInstallment, $installmentAmount, $savings) {
                // 1. Deduct from savings
                DB::table('savings_account_tbls')
                    ->where('id', $savings->id)
                    ->decrement('balance', $installmentAmount);

                if ($currentInstallment) {
                    // ── SCHEDULED LOAN — allocate the exact current installment ──
                    $allocation = $loanCalc->allocatePayment($loan->id, $installmentAmount);

                    // 2. Record the repayment
                    lending_repayments_tbl::create([
                        'lending_id' => $loan->id,
                        'user_id' => $status->user_id,
                        'payment_number' => (int) $currentInstallment->payment_number,
                        'amount_due' => $installmentAmount,
                        'amount_paid' => $installmentAmount,
                        'principal_paid' => $allocation['principal_paid'],
                        'interest_paid' => $allocation['interest_paid'],
                        'service_fee_paid' => 0,
                        'due_date' => $currentInstallment->due_date,
                        'payment_date' => now()->format('Y-m-d'),
                        'payment_method' => 'Savings Auto-Deduction',
                        'payment_type' => 'monthly',
                        'reference_no' => 'AUTO-'.now()->format('YmdHis'),
                        'notes' => 'Automatically deducted from savings due to overdue payment.',
                        'recorded_by' => null,
                    ]);

                    // 3. Update loan status
                    $status->total_paid = round((float) $status->total_paid + (float) $allocation['amount_paid'], 2);
                    $status->remaining_balance = max(0, round((float) $status->remaining_balance - $installmentAmount, 2));
                    $status->payments_made = (int) $status->payments_made + (int) $allocation['installments_settled'];

                    if ($allocation['fully_paid_now'] || $status->remaining_balance <= 0) {
                        $status->status = 'Completed';
                        $status->payments_made = $status->total_payments;
                        $status->remaining_balance = 0;
                        lending_program_tbl::where('id', $loan->id)->update(['status' => 'Completed']);
                    } else {
                        $nextUnpaid = $loanCalc->currentInstallment($loan->id);
                        if ($nextUnpaid) {
                            $status->due_date = $nextUnpaid->due_date;
                        }
                    }
                } else {
                    // ── LEGACY LOAN (no schedule) — unchanged ───────────────────
                    // 2. Record the repayment
                    lending_repayments_tbl::create([
                        'lending_id' => $loan->id,
                        'user_id' => $status->user_id,
                        'payment_number' => $status->payments_made + 1,
                        'amount_due' => $installmentAmount,
                        'amount_paid' => $installmentAmount,
                        'due_date' => $status->due_date,
                        'payment_date' => now()->format('Y-m-d'),
                        'payment_method' => 'Savings Auto-Deduction',
                        'payment_type' => 'monthly',
                        'reference_no' => 'AUTO-'.now()->format('YmdHis'),
                        'notes' => 'Automatically deducted from savings due to overdue payment.',
                        'recorded_by' => null,
                    ]);

                    // 3. Update loan status
                    $status->total_paid += $installmentAmount;
                    $status->remaining_balance = max(0, $status->remaining_balance - $installmentAmount);
                    $status->payments_made += 1;

                    if ($status->remaining_balance <= 0 || $status->payments_made >= $status->total_payments) {
                        $status->status = 'Completed';
                        $status->payments_made = $status->total_payments;
                        lending_program_tbl::where('id', $loan->id)->update(['status' => 'Completed']);
                    } else {
                        $status->due_date = \Carbon\Carbon::parse($loan->created_at)
                            ->addDays(($status->payments_made + 1) * 5) // keep in sync with PAYMENT_INTERVAL_DAYS
                            ->format('Y-m-d');
                    }
                }

                $status->save();

                AuditLog::log(
                    'Loan Auto-Repayment (Savings Deduction)',
                    "Auto-deducted ₱{$installmentAmount} from savings for overdue loan #{$loan->id}",
                    'loan',
                    $loan->id
                );
            });

            $this->info("Loan #{$loan->id}: ₱{$installmentAmount} deducted from savings.");
        }

        return Command::SUCCESS;
    }
}
