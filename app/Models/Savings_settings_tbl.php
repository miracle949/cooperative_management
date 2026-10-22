<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Savings_settings_tbl extends Model
{
    protected $table = 'savings_settings_tbls';

    protected $fillable = [
        'savings_type',
        'term_months',
        'interest_rate',
        'crediting_frequency',
        'min_amount',
        'max_amount',
        'is_active',
    ];

    protected $casts = [
        'interest_rate' => 'decimal:2',
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];
}
