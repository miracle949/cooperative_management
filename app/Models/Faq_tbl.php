<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq_tbl extends Model
{
    protected $table = 'faqs_tbls';

    protected $fillable = [
        'category',
        'question',
        'answer',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];
}