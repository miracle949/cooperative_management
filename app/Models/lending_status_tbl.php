<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class lending_status_tbl extends Model
{
    public $incrementing = true;

    protected $table = 'lending_status_tbls';

    protected $fillable = [
        'lending_id',
        'user_id',
        'remaining_balance',
        'total_paid',
        'payments_made',
        'total_payments',
        'interest_rate',
        'due_date',
        'status',
    ];
}
