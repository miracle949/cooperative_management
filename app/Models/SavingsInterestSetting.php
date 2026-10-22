<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavingsInterestSetting extends Model
{
    protected $table = 'savings_interest_settings_tbls';

    protected $fillable = [
        'annual_rate',
        'release_frequency',
        'min_balance_for_interest',
        'maintaining_balance',
    ];

    protected function casts(): array
    {
        return [
            'annual_rate' => 'decimal:2',
            'min_balance_for_interest' => 'decimal:2',
            'maintaining_balance' => 'decimal:2',
        ];
    }

    public static function getOrCreate(): self
    {
        return static::firstOrCreate([], [
            'annual_rate' => 2.00,
            'release_frequency' => 'quarterly',
            'min_balance_for_interest' => 0.00,
            'maintaining_balance' => 0.00,
        ]);
    }

    public function getFrequencyDivisorAttribute(): int
    {
        return match ($this->release_frequency) {
            'monthly' => 12,
            'quarterly' => 4,
            'semi-annual' => 2,
            'annual' => 1,
            default => 4,
        };
    }

    public function getFrequencyLabelAttribute(): string
    {
        return match ($this->release_frequency) {
            'monthly' => 'Monthly',
            'quarterly' => 'Quarterly',
            'semi-annual' => 'Semi-Annual',
            'annual' => 'Annual',
            default => 'Quarterly',
        };
    }
}
