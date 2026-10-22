<?php

namespace App\Services\PatronageSources;

use App\Models\PatronageRecord;

class AdditionalPatronageSource implements PatronageSource
{
    public function getName(): string
    {
        return 'Additional Patronage';
    }

    /**
     * Sums admin-entered additional patronage records for a member in a given year.
     * These cover outside-the-system services (gas, rice, oil, etc.)
     * that cannot be automatically tracked from within the system.
     */
    public function getPatronageForYear(int $userId, int $year): float
    {
        return (float) PatronageRecord::where('user_id', $userId)
            ->where('year', $year)
            ->sum('amount');
    }

    public function getTotalPatronageForYear(int $year): float
    {
        return (float) array_sum($this->getAllPatronageForYear($year));
    }

    public function getAllPatronageForYear(int $year): array
    {
        $results = PatronageRecord::where('year', $year)
            ->select('user_id')
            ->selectRaw('SUM(amount) as total')
            ->groupBy('user_id')
            ->get();

        $patronage = [];
        foreach ($results as $row) {
            $patronage[$row->user_id] = (float) $row->total;
        }

        return $patronage;
    }
}
