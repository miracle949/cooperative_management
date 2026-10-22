<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatronageRecord extends Model
{
    protected $table = 'patronage_records_tbls';

    protected $fillable = [
        'user_id',
        'year',
        'source',
        'description',
        'amount',
        'recorded_by',
    ];

    public function user()
    {
        return $this->belongsTo(Users_tbl::class, 'user_id');
    }

    public function recorder()
    {
        return $this->belongsTo(Users_tbl::class, 'recorded_by');
    }
}
