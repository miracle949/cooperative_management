<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CooperativeTransaction extends Model
{
    protected $table = 'cooperative_transactions_tbls';

    protected $fillable = [
        'description',
        'category',
        'transaction_type',
        'amount',
        'transaction_date',
    ];
}
