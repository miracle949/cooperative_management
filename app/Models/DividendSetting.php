<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DividendSetting extends Model
{
    protected $table = 'dividend_settings_tbls';

    protected $fillable = [
        'year',
        'dividend_fund_percentage',
        'patronage_fund_percentage',
        'patronage_basis',
        'reserve_fund_percentage',
        'cetf_percentage',
        'cdf_percentage',
        'optional_fund_percentage',
        'updated_by',
    ];

    public function updater()
    {
        return $this->belongsTo(Users_tbl::class, 'updated_by');
    }

    public static function getForYear($year)
    {
        return static::firstOrCreate(
            ['year' => $year],
            [
                'dividend_fund_percentage' => 60.00,
                'patronage_fund_percentage' => 40.00,
                'patronage_basis' => 'total_repayment',
                'reserve_fund_percentage' => 10.00,
                'cetf_percentage' => 10.00,
                'cdf_percentage' => 3.00,
                'optional_fund_percentage' => 7.00,
            ]
        );
    }
}
