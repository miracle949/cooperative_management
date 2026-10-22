<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeminarTypes_tbl extends Model
{
    protected $table = 'seminar_types_tbls';

    protected $fillable = [
        'slug',
        'label',
    ];
}
