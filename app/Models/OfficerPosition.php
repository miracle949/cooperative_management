<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfficerPosition extends Model
{
    protected $table = 'officer_positions_tbls';

    protected $fillable = ['name', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];
}