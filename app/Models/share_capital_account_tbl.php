<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class share_capital_account_tbl extends Model
{
    public $incrementing = true;

    protected $table = 'share_capital_account_tbls';

    protected $fillable = [
        'user_id',
        'total_shares',
        'total_amount',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(Users_tbl::class, 'user_id');
    }

    public function transactions()
    {
        return $this->hasMany(share_capital_transaction_tbl::class, 'share_capital_account_id');
    }
}
