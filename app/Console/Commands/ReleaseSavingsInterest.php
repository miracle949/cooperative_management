<?php

namespace App\Console\Commands;

use App\Http\Controllers\SavingsController;
use App\Http\Controllers\ShareCapital;
use App\Models\AuditLog;
use App\Models\savings_account_tbl;
use App\Models\savings_transaction_tbl;
use App\Models\SavingsInterestSetting;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Credits savings interest for the period that just ended.
 *
 * For every active savings account it:
 *   1. computes the balance as of the last day of the period,
 *   2. calculates interest = balance × annual_rate ÷ frequency_divisor,
 *   3. writes a COMPLETED `interest_credit` row to savings_transaction_tbls
 *      (this is what computeSavingsBalance() adds to the balance),
 *   4. writes a matching row to savings_interest_releases_tbls,
 *   5. re-syncs the stored balance column.
 *
 * Safe to run more than once: an account is never paid twice for the same period.
 *
 * Usage:
 *   php artisan savings:release-interest
 *   php artisan savings:release-interest --date=2026-10-01 --dry-run
 */
class ReleaseSavingsInterest extends Command
{
    protected $signature = 'savings:release-interest
                            {--date= : Treat this date (Y-m-d) as "today", e.g. 2026-10-01 releases September 2026}
                            {--dry-run : Show what would be credited without saving anything}';

    protected $description = 'Credit savings interest for the period that just ended into each member\'s savings balance.';

    public function handle(): int
    {
        $tz = 'Asia/Manila';
        $today = $this->option('date')
            ? Carbon::parse($this->option('date'), $tz)->startOfDay()
            : Carbon::now($tz)->startOfDay();
        $dryRun = (bool) $this->option('dry-run');

        $settings = SavingsInterestSetting::getOrCreate();
        $annualRate = (float) $settings->annual_rate;
        $divisor = max(1, (int) $settings->frequency_divisor);
        $minBalance = (float) ($settings->maintaining_balance ?? 0);

        [$start, $end, $label] = $this->lastCompletedPeriod($today, $settings->release_frequency);

        $this->info("Releasing interest for {$label} ({$start->toDateString()} to {$end->toDateString()}) at {$annualRate}% p.a."
            . ($dryRun ? ' [DRY RUN]' : ''));

        $credited = 0;
        $skipped = 0;
        $total = 0.0;

        $accounts = savings_account_tbl::whereRaw('LOWER(status) = ?', ['active'])->get();

        foreach ($accounts as $account) {
            // Already paid for this period → never pay twice.
            $alreadyReleased = DB::table('savings_interest_releases_tbls')
                ->where('savings_account_id', $account->id)
                ->whereDate('period_start', $start->toDateString())
                ->exists();

            if ($alreadyReleased) {
                $skipped++;
                continue;
            }

            // Account opened after the period ended → no interest for that period.
            if ($account->opened_at && Carbon::parse($account->opened_at)->gt($end)) {
                continue;
            }

            $balance = $this->balanceAsOf($account->id, $end);

            // No interest on empty accounts or accounts below the maintaining balance.
            if ($balance <= 0 || ($minBalance > 0 && $balance < $minBalance)) {
                continue;
            }

            $interest = round($balance * ($annualRate / 100) / $divisor, 2);

            if ($interest < 0.01) {
                continue;
            }

            $reference = 'INT-' . $start->format('Ym') . '-' . str_pad((string) $account->id, 5, '0', STR_PAD_LEFT);

            if ($dryRun) {
                $this->line("  Account #{$account->id}: balance ₱" . number_format($balance, 2)
                    . " → interest ₱" . number_format($interest, 2) . " ({$reference})");
                $credited++;
                $total += $interest;
                continue;
            }

            DB::transaction(function () use ($account, $interest, $reference, $start, $end, $label, $annualRate, $today) {
                $savings = new SavingsController;
                $currentBalance = $savings->computeSavingsBalance($account->id);

                // This row is what actually raises the balance (computeSavingsBalance
                // sums completed interest_credit rows).
                savings_transaction_tbl::create([
                    'savings_account_id' => $account->id,
                    'type' => 'interest_credit',
                    'amount' => $interest,
                    'payment_method' => 'Auto-credited',
                    'balance_after' => round($currentBalance + $interest, 2),
                    'note' => "Interest for {$label} ({$annualRate}% p.a.)",
                    'reference_no' => $reference,
                    'transaction_date' => $today->toDateString(),
                    'status' => 'completed',
                ]);

                DB::table('savings_interest_releases_tbls')->insert([
                    'savings_account_id' => $account->id,
                    'period_start' => $start->toDateString(),
                    'period_end' => $end->toDateString(),
                    'period_label' => $label,
                    'amount' => $interest,
                    'reference_no' => $reference,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Re-sync the stored balance column.
                $savings->computeSavingsBalance($account->id);
            });

            $credited++;
            $total += $interest;
        }

        $summary = "Savings interest for {$label}: credited {$credited} account(s), total ₱"
            . number_format($total, 2) . ", skipped {$skipped} already-released account(s).";

        $this->info($summary . ($dryRun ? ' (dry run — nothing saved)' : ''));

        if (!$dryRun && $credited > 0) {
            try {
                AuditLog::log('Savings Interest Released', $summary, 'savings', null);
            } catch (\Throwable $e) {
                // Audit logging must never block the release itself.
                $this->warn('Audit log not written: ' . $e->getMessage());
            }
        }

        return self::SUCCESS;
    }

    /**
     * The most recent fully-finished period before $today, based on the
     * release frequency set in Savings Interest Settings.
     *
     * @return array{0: Carbon, 1: Carbon, 2: string} [start, end, label]
     */
    private function lastCompletedPeriod(Carbon $today, ?string $frequency): array
    {
        switch ($frequency) {
            case 'quarterly':
                $end = $today->copy()->firstOfQuarter()->subDay();
                $start = $end->copy()->firstOfQuarter();

                return [$start, $end, 'Q' . $end->quarter . ' ' . $end->year];

            case 'semi-annual':
                $halfStart = Carbon::create($today->year, $today->month < 7 ? 1 : 7, 1, 0, 0, 0, $today->timezone);
                $end = $halfStart->copy()->subDay();
                $start = Carbon::create($end->year, $end->month <= 6 ? 1 : 7, 1, 0, 0, 0, $today->timezone);

                return [$start, $end, ($end->month <= 6 ? 'H1 ' : 'H2 ') . $end->year];

            case 'annual':
                $end = $today->copy()->startOfYear()->subDay();
                $start = $end->copy()->startOfYear();

                return [$start, $end, (string) $end->year];

            case 'monthly':
            default:
                $end = $today->copy()->startOfMonth()->subDay();
                $start = $end->copy()->startOfMonth();

                return [$start, $end, $start->format('F Y')];
        }
    }

    /**
     * Balance as of the end of a day — same rules as
     * SavingsController::computeSavingsBalance(), limited by transaction date.
     */
    private function balanceAsOf(int $accountId, Carbon $end): float
    {
        $base = fn() => savings_transaction_tbl::where('savings_account_id', $accountId)
            ->whereRaw('LOWER(status) = ?', ['completed'])
            ->whereDate('transaction_date', '<=', $end->toDateString());

        $credits = $base()->whereIn('type', ['deposit', 'interest_credit'])->sum('amount');
        $debits = $base()->whereIn('type', ['withdrawal', ShareCapital::CONVERSION_TYPE])->sum('amount');

        return round((float) $credits - (float) $debits, 2);
    }
}