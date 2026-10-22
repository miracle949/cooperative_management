<?php

namespace App\Services\PatronageSources;

use App\Models\DividendSetting;
use Illuminate\Support\Facades\DB;

class LoanRepaymentPatronageSource implements PatronageSource
{
    public function getName(): string
    {
        return 'Loan Repayments';
    }

    /**
     * Patronage from loan repayments is based on income components only:
     *   interest_paid + service_fee_paid + COALESCE(late_fee, 0)
     *
     * The patronage_basis setting controls whether late fees are included:
     *   - 'total_repayment': includes late fees (income components + late_fee)
     *   - 'net_repayment': excludes late fees (income components only)
     *
     * Fallback: only when the income breakdown is genuinely unavailable
     * (interest_paid or service_fee_paid is NULL for legacy records) does the
     * full amount_paid count as patronage. Legitimate zero-income repayments
     * (columns present, value 0) are NOT treated as patronage, so principal
     * repayment is never counted when an income breakdown exists.
     */
    public function getPatronageForYear(int $userId, int $year): float
    {
        $basis = $this->getPatronageBasis($year);

        $query = DB::table('lending_repayments_tbls')
            ->where('user_id', $userId)
            ->whereYear('payment_date', $year);

        if ($basis === 'net_repayment') {
            return (float) $query->sum(DB::raw(
                'CASE WHEN (interest_paid IS NULL OR service_fee_paid IS NULL) '
                .'THEN amount_paid '
                .'ELSE (COALESCE(interest_paid, 0) + COALESCE(service_fee_paid, 0)) END'
            ));
        }

        // Default: total_repayment (includes late fees)
        return (float) $query->sum(DB::raw(
            'CASE WHEN (interest_paid IS NULL OR service_fee_paid IS NULL) '
            .'THEN amount_paid '
            .'ELSE (COALESCE(interest_paid, 0) + COALESCE(service_fee_paid, 0) + COALESCE(late_fee, 0)) END'
        ));
    }

    public function getTotalPatronageForYear(int $year): float
    {
        return (float) array_sum($this->getAllPatronageForYear($year));
    }

    public function getAllPatronageForYear(int $year): array
    {
        $basis = $this->getPatronageBasis($year);

        $incomeExpr = $basis === 'net_repayment'
            ? 'CASE WHEN (interest_paid IS NULL OR service_fee_paid IS NULL) THEN amount_paid ELSE (COALESCE(interest_paid, 0) + COALESCE(service_fee_paid, 0)) END'
            : 'CASE WHEN (interest_paid IS NULL OR service_fee_paid IS NULL) THEN amount_paid ELSE (COALESCE(interest_paid, 0) + COALESCE(service_fee_paid, 0) + COALESCE(late_fee, 0)) END';

        $results = DB::table('lending_repayments_tbls')
            ->whereYear('payment_date', $year)
            ->select('user_id')
            ->selectRaw("user_id, SUM({$incomeExpr}) as total")
            ->groupBy('user_id')
            ->get();

        $patronage = [];
        foreach ($results as $row) {
            $patronage[$row->user_id] = (float) $row->total;
        }

        return $patronage;
    }

    private function getPatronageBasis(int $year): string
    {
        // The stored dividend_distributions.patronage_basis is the authoritative
        // snapshot for an already-generated distribution. Only fall back to the
        // live DividendSetting when no snapshot exists (e.g. brand-new year).
        $stored = DB::table('dividend_distributions')
            ->where('year', $year)
            ->value('patronage_basis');

        if (! empty($stored)) {
            $basis = $stored;
        } else {
            $setting = DividendSetting::where('year', $year)->first();
            $basis = $setting->patronage_basis ?? 'total_repayment';
        }

        // Only whitelisted bases are ever used to build the SQL income
        // expression below; anything unexpected falls back safely.
        if (! in_array($basis, ['total_repayment', 'net_repayment'], true)) {
            return 'total_repayment';
        }

        return $basis;
    }
}
