<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResignationRequest_tbl extends Model
{
    protected $table = 'resignation_requests_tbls';

    protected $fillable = [
        'user_id',
        'withdraw_share_capital',
        'status',
        'approved_at',
        'release_date',
        'is_released',
    ];

    protected $casts = [
        'withdraw_share_capital' => 'boolean',
        'is_released' => 'boolean',
        'approved_at' => 'datetime',
        'release_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(Users_tbl::class, 'user_id');
    }
}
