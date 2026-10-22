<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeDeposit extends Model
{
    protected $table = 'time_deposits_tbl';

    protected $fillable = [
        'savings_account_id',
        'goal_amount',
        'balance',
        'interest_rate',
        'term_months',
        'opened_at',
        'maturity_date',
        'status',
        'reference_no',
        'claim_reference_no',
        'claimed_at',
        // ★ ADD THESE THREE — missing from fillable is why they saved as NULL
        'claimed_amount',
        'claimed_principal',
        'claimed_interest',
        'interest_accrued_balance',
        'last_interest_credited_at',
    ];

    protected $casts = [
        'opened_at' => 'date',
        'maturity_date' => 'date',
        'claimed_at' => 'datetime',
    ];
}
