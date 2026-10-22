<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class officer_tbl extends Model
{
    public $incrementing = true;

    protected $table = 'officers_tbls';

    protected $fillable = [
        'user_id',
        'position',
        'term_start',
        'term_end',
        'sort_order',
    ];

    protected $casts = [
        'term_start' => 'date',
        'term_end' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(Users_tbl::class, 'user_id');
    }
}
