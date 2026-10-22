<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatronageRefundDistribution extends Model
{
    protected $table = 'patronage_refund_distributions_tbls';

    protected $fillable = [
        'user_id',
        'year',
        'total_patronage',
        'allocation_ratio',
        'amount',
        'status',
        'approved_at',
        'disbursed_at',
    ];

    protected $casts = [
        'total_patronage' => 'decimal:2',
        'allocation_ratio' => 'decimal:4',
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'disbursed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(Users_tbl::class, 'user_id');
    }
}
