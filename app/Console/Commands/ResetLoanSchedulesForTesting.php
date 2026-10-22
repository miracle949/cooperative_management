<?php

namespace App\Console\Commands;

use App\Models\lending_program_tbl;
use App\Models\lending_status_tbl;
use App\Models\Loan_settings_tbl;
use App\Services\LoanCalculationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ResetLoanSchedulesForTesting extends Command
{
    protected $signature = 'loans:reset-schedules
        {--ids= : Comma-separated loan IDs to reset (defaults to all Approved/Completed loans)}
        {--dry-run : Preview what would change without writing}';

    protected $description = 'Reset existing test loans onto the new diminishing-balance installment schedule (idempotent; backs up affected rows first)';

    public function handle()
    {
        $ids = $this->option('ids')
            ? array_map('intval', explode(',', (string) $this->option('ids')))
            : [];

        $loans = lending_program_tbl::query()
            ->when($ids, fn ($q) => $q->whereIn('id', $ids))
            ->whereIn('status', ['Approved', 'Completed'])
            ->orderBy('id')
            ->get();

        if ($loans->isEmpty()) {
            $this->warn('No Approved/Completed loans to reset.');

            return Command::SUCCESS;
        }

        $loanCalc = new LoanCalculationService;
        $dryRun = (bool) $this->option('dry-run');

        $this->info('Loans to reset: '.$loans->pluck('id')->implode(', '));
        $this->newLine();

        if (! $dryRun) {
            $backup = $this->backup($loans->pluck('id')->all());
            $this->info('Backup written to: '.$backup);
            $this->newLine();
        }

        $rows = [];

        foreach ($loans as $loan) {
            $principal = (float) $loan->lending_amount;
            $term = max(1, (int) filter_var($loan->lending_type_term ?? '6', FILTER_SANITIZE_NUMBER_INT));
            $rate = $this->rateForLoanType($loan->lending_type);
            $schedule = $loanCalc->buildSchedule($principal, $rate, $term);

            $rows[] = [
                'id' => $loan->id,
                'reference_no' => $loan->reference_no,
                'type' => $loan->lending_type,
                'amount' => $principal,
                'term' => $term,
                'rate' => $rate,
                'old_total' => (float) ($loan->total_payment ?? 0),
                'new_total' => $schedule['total_payment'],
                'new_interest' => $schedule['total_interest'],
                'installments' => count($schedule['installments']),
            ];

            if ($dryRun) {
                continue;
            }

            DB::transaction(function () use ($loan, $loanCalc, $schedule, $term) {
                // Remove conflicting repayment + schedule records.
                DB::table('lending_repayments_tbls')->where('lending_id', $loan->id)->delete();
                DB::table('lending_installment_schedules_tbls')->where('lending_id', $loan->id)->delete();

                // Regenerate the installment schedule with fresh due dates.
                $loanCalc->persistSchedule($loan->id, $schedule);

                // Reset the loan's repayment-related values (application/approval
                // info such as amount, type, term, member, fees stay intact).
                $loan->status = 'Approved';
                $loan->total_payment = $schedule['total_payment'];
                $loan->total_interest = $schedule['total_interest'];
                $loan->monthly_payment = $loanCalc->averageMonthlyPayment($schedule);
                $loan->due_date = now()->timezone('Asia/Manila')->addMonths($term)->format('Y-m-d');
                $loan->late_fee = 0;
                $loan->penalty_applied_at = null;
                $loan->save();

                $firstDue = $schedule['installments'][0]['due_date']
                    ?? now()->addDays(LoanCalculationService::PAYMENT_INTERVAL_DAYS)->format('Y-m-d');
                $interestRate = $loan->lending_amount > 0
                    ? round(($schedule['total_interest'] / $loan->lending_amount) * 100, 2)
                    : 0;

                $status = lending_status_tbl::where('lending_id', $loan->id)->first()
                    ?? new lending_status_tbl(['lending_id' => $loan->id, 'user_id' => $loan->user_id]);

                $status->remaining_balance = $schedule['total_payment'];
                $status->total_paid = 0;
                $status->payments_made = 0;
                $status->total_payments = $term;
                $status->interest_rate = $interestRate;
                $status->penalty_amount = 0;
                $status->last_penalty_date = null;
                $status->due_date = $firstDue;
                $status->status = 'Active';
                $status->save();
            });
        }

        // Purge orphaned schedule rows (leftover schedules for deleted loans).
        $existingIds = lending_program_tbl::pluck('id')->all();
        $purged = 0;
        if (! $dryRun) {
            $orphanLendingIds = DB::table('lending_installment_schedules_tbls')
                ->distinct()
                ->pluck('lending_id')
                ->reject(fn ($id) => in_array($id, $existingIds));
            foreach ($orphanLendingIds as $orphanId) {
                $purged += DB::table('lending_installment_schedules_tbls')->where('lending_id', $orphanId)->delete();
            }
        }

        $this->table(
            ['ID', 'Ref', 'Type', 'Amount', 'Term', 'Rate', 'Old Total', 'New Total', 'New Interest', '# Installments'],
            $rows
        );

        if ($dryRun) {
            $this->info('DRY RUN — no changes written.');

            return Command::SUCCESS;
        }

        $this->info('Purged orphaned schedule rows: '.$purged);
        $this->info('Reset '.$loans->count().' loan(s) onto the new schedule system.');

        return Command::SUCCESS;
    }

    private function rateForLoanType(?string $lendingType): float
    {
        $type = trim((string) $lendingType);
        $setting = Loan_settings_tbl::where('loan_type', $type)->first();

        if (! $setting) {
            $setting = Loan_settings_tbl::where('loan_type', str_ireplace(' Lending', ' Loan', $type))->first();
        }

        return $setting ? (float) ($setting->interest_rate ?? 2.00) : 2.00;
    }

    private function backup(array $loanIds): string
    {
        $dir = storage_path('app/backups');
        File::ensureDirectoryExists($dir);
        $path = $dir.'/loan_reset_backup_'.now()->format('Ymd_His').'.json';

        $data = [
            'exported_at' => now()->toDateTimeString(),
            'loans' => lending_program_tbl::whereIn('id', $loanIds)->get()->toArray(),
            'statuses' => lending_status_tbl::whereIn('lending_id', $loanIds)->get()->toArray(),
            'repayments' => DB::table('lending_repayments_tbls')->whereIn('lending_id', $loanIds)->get()->toArray(),
            'schedules' => DB::table('lending_installment_schedules_tbls')->whereIn('lending_id', $loanIds)->get()->toArray(),
        ];

        File::put($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return $path;
    }
}
