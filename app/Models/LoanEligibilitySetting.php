<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanEligibilitySetting extends Model
{
    protected $table = 'loan_eligibility_settings_tbls';

    protected $fillable = [
        'savings_to_loan_enabled',
        'savings_to_loan_ratio',
        'minimum_shares',
    ];

    protected function casts(): array
    {
        return [
            'savings_to_loan_enabled' => 'boolean',
            'savings_to_loan_ratio' => 'decimal:2',
            'minimum_shares' => 'decimal:2',
        ];
    }

    public static function getOrCreate(): self
    {
        return static::firstOrCreate([], [
            'savings_to_loan_enabled' => true,
            'savings_to_loan_ratio' => 100.00,
            'minimum_shares' => 10.00,
        ]);
    }
}
