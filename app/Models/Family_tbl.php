<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class family_tbl extends Model
{
    protected $table = 'family_tbls';

    protected $fillable = [
        'user_id',
        'spouse_name',
        'spouse_date_birth',
        'spouse_place_birth',
        'number_son',
        'number_daughter',
        'other_spec',
    ];
}
