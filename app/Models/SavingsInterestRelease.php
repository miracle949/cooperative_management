<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavingsInterestRelease extends Model
{
    protected $table = 'savings_interest_releases_tbls';

    protected $fillable = [
        'savings_account_id',
        'period_start',
        'period_end',
        'period_label',
        'amount',
        'reference_no',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function savingsAccount()
    {
        return $this->belongsTo(savings_account_tbl::class, 'savings_account_id');
    }

    public static function existsForPeriod(int $accountId, string $periodStart, string $periodEnd): bool
    {
        return static::where('savings_account_id', $accountId)
            ->where('period_start', $periodStart)
            ->where('period_end', $periodEnd)
            ->exists();
    }

    public static function isPeriodReleasedAnyAccount(string $periodStart, string $periodEnd): bool
    {
        return static::where('period_start', $periodStart)
            ->where('period_end', $periodEnd)
            ->exists();
    }
}
