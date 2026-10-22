<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeminarCompletions_tbl extends Model
{
    protected $table = 'seminar_completions_tbls';

    protected $fillable = [
        'user_id',
        'pmes_completed',
        'fundamentals_completed',
        'finance_completed',
        'completed_at',
    ];

    protected $casts = [
        'pmes_completed' => 'boolean',
        'fundamentals_completed' => 'boolean',
        'finance_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(Users_tbl::class, 'user_id');
    }
}
