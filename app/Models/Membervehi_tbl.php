<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Membervehi_tbl extends Model
{
    protected $table = 'membervehi_tbls';

    protected $fillable = [
        'user_id',
        'plate_no',
        'vehicle_type',
        'quantity',
    ];
}
