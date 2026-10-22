<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class dividend_rates_tbl extends Model
{
    protected $table = 'dividend_rates_tbls';

    protected $fillable = [
        'rate',
        'effective_year',
    ];
}
