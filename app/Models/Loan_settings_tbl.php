<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Loan_settings_tbl extends Model
{
    protected $table = 'loan_settings_tbls';

    protected $fillable = [
        'loan_type',
        'interest_rate',
        'max_amount',
        'late_fee_percentage',
        'grace_period_months',
    ];

    public static function getRate($loanType)
    {
        $setting = self::where('loan_type', $loanType)->first();

        return $setting ? $setting->interest_rate : 2.00;
    }

    /**
     * The dynamic late-fee percentage (%) for a loan type, read from the
     * Finance settings. Falls back to 2.00 when no settings row exists.
     */
    public static function getLateFeeRate($loanType)
    {
        $setting = self::where('loan_type', $loanType)->first();

        if (! $setting) {
            $normalized = str_ireplace(' Lending', ' Loan', trim((string) $loanType));
            $setting = self::where('loan_type', $normalized)->first();
        }

        return $setting ? (float) ($setting->late_fee_percentage ?? 2.00) : 2.00;
    }

    public static function getAllRates()
    {
        return self::pluck('interest_rate', 'loan_type')->toArray();
    }
}
