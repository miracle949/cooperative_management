<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dividend extends Model
{
    protected $fillable = [
        'user_id',
        'year',
        'share_capital_amount',
        'recommended_amount',
        'approved_amount',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(Users_tbl::class, 'user_id');
    }
}
