<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class savings_account_tbl extends Model
{
    public $incrementing = true;

    protected $table = 'savings_account_tbls';

    protected $fillable = [
        'user_id',
        'balance',
        'status',
        'opened_at',
        'td_balance',
        'td_goal_amount',
        'td_interest_rate',
        'td_term_months',
        'td_opened_at',
        'td_maturity_date',
        'td_status',
        'td_reference_no',
        'interest_accrued_balance',
    ];

    public function user()
    {
        return $this->belongsTo(Users_tbl::class, 'user_id');
    }

    public function transactions()
    {
        return $this->hasMany(savings_transaction_tbl::class, 'savings_account_id');
    }
}
