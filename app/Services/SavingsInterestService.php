<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\savings_account_tbl;
use App\Models\savings_transaction_tbl;
use App\Models\SavingsInterestRelease;
use App\Models\SavingsInterestSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SavingsInterestService
{
    public function getSettings(): SavingsInterestSetting
    {
        return SavingsInterestSetting::getOrCreate();
    }

    /**
     * Average daily balance over [start, end], inclusive.
     * Walks day-by-day using balance_after snapshots from savings_transaction_tbl.
     */
    public function averageDailyBalance(savings_account_tbl $account, Carbon $start, Carbon $end): float
    {
        $days = $start->diffInDays($end) + 1;

        $priorTx = savings_transaction_tbl::where('savings_account_id', $account->id)
            ->where('transaction_date', '<', $start->toDateString())
            ->whereNotNull('balance_after')
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();

        $runningBalance = $priorTx ? (float) $priorTx->balance_after : 0.0;

        $txsInPeriod = savings_transaction_tbl::where('savings_account_id', $account->id)
            ->whereBetween('transaction_date', [$start->toDateString(), $end->toDateString()])
            ->whereNotNull('balance_after')
            ->orderBy('transaction_date')
            ->orderBy('created_at')
            ->get()
            ->groupBy(fn ($tx) => Carbon::parse($tx->transaction_date)->toDateString());

        $weightedSum = 0.0;
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            $key = $cursor->toDateString();
            if (isset($txsInPeriod[$key])) {
                $runningBalance = (float) $txsInPeriod[$key]->last()->balance_after;
            }
            $weightedSum += $runningBalance;
            $cursor->addDay();
        }

        return $days > 0 ? $weightedSum / $days : 0.0;
    }

    /**
     * Compute the most recently completed period based on frequency.
     * Returns [$periodStart, $periodEnd, $periodLabel] or null if no new period is due.
     *
     * Catch-up behavior: if the trigger day was missed (e.g. server was down),
     * the period is still released on any subsequent day within the catch-up window,
     * as long as it hasn't been released yet.
     */
    public function getMostRecentCompletedPeriod(?Carbon $now = null): ?array
    {
        $now = $now ?? Carbon::now();
        $frequency = $this->getSettings()->release_frequency;

        return match ($frequency) {
            'monthly' => $this->getPreviousMonthPeriod($now),
            'quarterly' => $this->getPreviousQuarterPeriod($now),
            'semi-annual' => $this->getPreviousSemiAnnualPeriod($now),
            'annual' => $this->getPreviousAnnualPeriod($now),
            default => null,
        };
    }

    private function getPreviousMonthPeriod(Carbon $now): ?array
    {
        $prevMonth = $now->copy()->subMonth();
        $start = $prevMonth->copy()->startOfMonth()->startOfDay();
        $end = $prevMonth->copy()->endOfMonth()->endOfDay();
        $label = $start->format('M Y');

        return [$start, $end, $label];
    }

    private function getPreviousQuarterPeriod(Carbon $now): ?array
    {
        $isQuarterStart = $now->month === 1 || $now->month === 4 || $now->month === 7 || $now->month === 10;
        if (! $isQuarterStart) {
            return null;
        }

        $prevQuarter = $now->copy()->subMonthsNoOverflow(3);
        $qStartMonth = (intdiv($prevQuarter->month - 1, 3) * 3) + 1;
        $start = Carbon::create($prevQuarter->year, $qStartMonth, 1)->startOfDay();
        $end = $start->copy()->addMonthsNoOverflow(3)->subDay()->endOfDay();
        $qNumber = intdiv($qStartMonth - 1, 3) + 1;
        $label = "Q{$qNumber} {$start->year}";

        return [$start, $end, $label];
    }

    private function getPreviousSemiAnnualPeriod(Carbon $now): ?array
    {
        $isHalfStart = $now->month === 1 || $now->month === 7;
        if (! $isHalfStart) {
            return null;
        }

        $prevHalf = $now->copy()->subMonthsNoOverflow(6);
        $startMonth = $prevHalf->month < 7 ? 1 : 7;
        $start = Carbon::create($prevHalf->year, $startMonth, 1)->startOfDay();
        $end = $start->copy()->addMonthsNoOverflow(6)->subDay()->endOfDay();
        $halfLabel = $startMonth === 1 ? 'H1' : 'H2';
        $label = "{$halfLabel} {$start->year}";

        return [$start, $end, $label];
    }

    private function getPreviousAnnualPeriod(Carbon $now): ?array
    {
        if ($now->month !== 1) {
            return null;
        }

        $prevYear = $now->year - 1;
        $start = Carbon::create($prevYear, 1, 1)->startOfDay();
        $end = Carbon::create($prevYear, 12, 31)->endOfDay();
        $label = (string) $prevYear;

        return [$start, $end, $label];
    }

    /**
     * Credit interest for one account for a given period.
     * Returns details if credited, null if skipped.
     */
    public function releaseInterestForAccount(
        savings_account_tbl $account,
        Carbon $periodStart,
        Carbon $periodEnd,
        string $periodLabel
    ): ?array {
        if (SavingsInterestRelease::existsForPeriod($account->id, $periodStart->toDateString(), $periodEnd->toDateString())) {
            return null;
        }

        $settings = $this->getSettings();

        if ($settings->min_balance_for_interest > 0 && (float) $account->balance < (float) $settings->min_balance_for_interest) {
            return null;
        }

        $avgBalance = $this->averageDailyBalance($account, $periodStart, $periodEnd);

        $periodRate = (float) $settings->annual_rate / 100 / $settings->frequency_divisor;
        $interest = round($avgBalance * $periodRate, 2);

        if ($interest <= 0) {
            return null;
        }

        $referenceNo = 'INT-'.strtoupper(bin2hex(random_bytes(3))).'-'.$periodEnd->format('Ymd');

        return DB::transaction(function () use ($account, $interest, $avgBalance, $periodStart, $periodEnd, $periodLabel, $referenceNo, $settings) {
            $newInterestBalance = (float) $account->interest_accrued_balance + $interest;
            $newBalance = round((float) $account->balance + $interest, 2);

            $account->update([
                'balance' => $newBalance,
                'interest_accrued_balance' => $newInterestBalance,
                'interest_last_credited_at' => $periodEnd->toDateString(),
            ]);

            savings_transaction_tbl::create([
                'savings_account_id' => $account->id,
                'type' => 'interest_credit',
                'amount' => $interest,
                'payment_method' => 'System',
                'balance_after' => $newBalance,
                'note' => sprintf(
                    '%s interest (%.2f%% p.a.) for %s–%s · Avg daily balance ₱%s',
                    $settings->frequency_label,
                    $settings->annual_rate,
                    $periodStart->format('M d'),
                    $periodEnd->format('M d, Y'),
                    number_format($avgBalance, 2)
                ),
                'reference_no' => $referenceNo,
                'transaction_date' => $periodEnd->toDateString(),
                'status' => 'completed',
            ]);

            SavingsInterestRelease::create([
                'savings_account_id' => $account->id,
                'period_start' => $periodStart->toDateString(),
                'period_end' => $periodEnd->toDateString(),
                'period_label' => $periodLabel,
                'amount' => $interest,
                'reference_no' => $referenceNo,
            ]);

            AuditLog::log(
                'Savings Interest Credited',
                "Credited ₱{$interest} interest to savings account #{$account->id} for {$periodLabel} (Ref: {$referenceNo})",
                'savings',
                $account->id
            );

            return [
                'account_id' => $account->id,
                'interest' => $interest,
                'avg_balance' => $avgBalance,
                'reference_no' => $referenceNo,
                'period_label' => $periodLabel,
            ];
        });
    }

    /**
     * Auto-release interest for all active accounts if a new period is due.
     * Safe to call repeatedly (idempotent per period).
     * One account failing does not stop other accounts from receiving interest.
     */
    public function autoRelease(?Carbon $now = null): array
    {
        $period = $this->getMostRecentCompletedPeriod($now);

        if (! $period) {
            return [];
        }

        [$periodStart, $periodEnd, $periodLabel] = $period;

        $results = [];
        $errors = [];

        savings_account_tbl::where('status', 'active')
            ->chunkById(100, function ($accounts) use (&$results, &$errors, $periodStart, $periodEnd, $periodLabel) {
                foreach ($accounts as $account) {
                    try {
                        $result = $this->releaseInterestForAccount($account, $periodStart, $periodEnd, $periodLabel);
                        if ($result) {
                            $results[] = $result;
                        }
                    } catch (\Throwable $e) {
                        $errors[] = [
                            'account_id' => $account->id,
                            'period' => $periodStart->toDateString().' – '.$periodEnd->toDateString(),
                            'message' => $e->getMessage(),
                            'file' => $e->getFile(),
                            'line' => $e->getLine(),
                        ];
                        Log::error("Savings interest release failed for account #{$account->id}", [
                            'account_id' => $account->id,
                            'period_start' => $periodStart->toDateString(),
                            'period_end' => $periodEnd->toDateString(),
                            'period_label' => $periodLabel,
                            'error' => $e->getMessage(),
                            'exception' => $e,
                        ]);
                    }
                }
            });

        if (! empty($errors)) {
            Log::warning('Savings interest auto-release completed with '.count($errors).' error(s)', [
                'period_label' => $periodLabel,
                'errors' => $errors,
            ]);
        }

        return $results;
    }
}
