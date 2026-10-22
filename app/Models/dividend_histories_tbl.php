<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class dividend_histories_tbl extends Model
{
    protected $table = 'dividend_histories_tbls';

    protected $fillable = [
        'share_capital_account_id',
        'period_label',
        'semester',
        'year',
        'dividend_rate',
        'share_capital',
        'date_paid',
        'status',
    ];
}
