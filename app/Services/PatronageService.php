<?php

namespace App\Services;

use App\Models\PatronageRefundDistribution;
use App\Models\Users_tbl;
use App\Services\PatronageSources\AdditionalPatronageSource;
use App\Services\PatronageSources\LoanRepaymentPatronageSource;
use App\Services\PatronageSources\PatronageSource;
use Illuminate\Support\Facades\DB;

class PatronageService
{
    private array $sources;

    public function __construct()
    {
        $this->sources = [
            new LoanRepaymentPatronageSource,
            new AdditionalPatronageSource,
        ];
    }

    /**
     * Register a custom patronage source.
     * Call this to add future sources without modifying the service.
     */
    public function addSource(PatronageSource $source): self
    {
        $this->sources[] = $source;

        return $this;
    }

    /**
     * Get all registered patronage sources.
     */
    public function getSources(): array
    {
        return $this->sources;
    }

    /**
     * Calculate total patronage for a single member in a given year.
     * Aggregates across all registered sources.
     */
    public function calculateMemberPatronage(int $userId, int $year): float
    {
        $total = 0.0;
        foreach ($this->sources as $source) {
            $total += $source->getPatronageForYear($userId, $year);
        }

        return round($total, 2);
    }

    /**
     * Calculate patronage for all members in a given year.
     * Returns Collection of [user_id => total_patronage].
     */
    public function calculateAllPatronage(int $year): \Illuminate\Support\Collection
    {
        $allPatronage = [];

        foreach ($this->sources as $source) {
            $sourceData = $source->getAllPatronageForYear($year);
            foreach ($sourceData as $userId => $amount) {
                if (! isset($allPatronage[$userId])) {
                    $allPatronage[$userId] = 0.0;
                }
                $allPatronage[$userId] += $amount;
            }
        }

        return collect($allPatronage)->map(fn ($amount) => round($amount, 2));
    }

    /**
     * Generate patronage refund distribution records for all members.
     *
     * Rules:
     * - If any approved or disbursed records exist for the year, throw an exception.
     *   Admin must reset the distribution first.
     * - If only pending records exist, they are deleted and regenerated.
     * - The patronage pool amount is read from dividend_distributions.
     * - Each member's record is a snapshot at generation time.
     */
    public function generatePatronageRefundDistributions(int $year): void
    {
        $hasApproved = PatronageRefundDistribution::where('year', $year)
            ->where('status', 'approved')
            ->exists();

        $hasDisbursed = PatronageRefundDistribution::where('year', $year)
            ->where('status', 'disbursed')
            ->exists();

        if ($hasApproved || $hasDisbursed) {
            throw new \RuntimeException(
                'Cannot regenerate patronage refund distributions. '.
                'Approved or disbursed records exist for year '.$year.'. '.
                'Reset the annual distribution first.'
            );
        }

        $distribution = DB::table('dividend_distributions')
            ->where('year', $year)
            ->first();

        if (! $distribution) {
            throw new \RuntimeException(
                'No dividend distribution found for year '.$year.'. '.
                'Generate the annual distribution first.'
            );
        }

        $patronagePool = (float) $distribution->patronage_refund_pool;

        DB::beginTransaction();

        try {
            // Delete existing pending records for this year
            PatronageRefundDistribution::where('year', $year)
                ->where('status', 'pending')
                ->delete();

            // Calculate patronage for all members
            $allPatronage = $this->calculateAllPatronage($year);
            $totalPatronage = $allPatronage->sum();

            if ($totalPatronage <= 0) {
                DB::rollBack();
                throw new \RuntimeException(
                    'No patronage data was found for the selected fiscal year. Patronage refunds cannot be generated.'
                );
            }

            // Get all members with role 'member'
            $members = Users_tbl::where('role', 'member')->pluck('id');

            foreach ($members as $userId) {
                $memberPatronage = $allPatronage->get($userId, 0.0);

                $allocationRatio = 0.0;
                $amount = 0.0;

                if ($totalPatronage > 0 && $memberPatronage > 0) {
                    $allocationRatio = round($memberPatronage / $totalPatronage, 4);
                    $amount = round(($memberPatronage / $totalPatronage) * $patronagePool, 2);
                }

                PatronageRefundDistribution::create([
                    'user_id' => $userId,
                    'year' => $year,
                    'total_patronage' => $memberPatronage,
                    'allocation_ratio' => $allocationRatio,
                    'amount' => $amount,
                    'status' => 'pending',
                ]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
