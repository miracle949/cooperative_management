<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class educational_tbl extends Model
{
    protected $table = 'educational_tbls';

    protected $fillable = [
        'user_id',
        'educational_level',
        'status',
        'specify',
    ];
}
