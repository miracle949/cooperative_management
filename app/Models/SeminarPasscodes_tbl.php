<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeminarPasscodes_tbl extends Model
{
    protected $table = 'seminar_passcodes_tbls';

    protected $fillable = [
        'seminar_type',
        'passcode',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];
}
