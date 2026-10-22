<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notifications_tbl extends Model
{
    protected $table = 'notifications_tbls';

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'category',
        'is_important',
        'is_read',
    ];

    protected $casts = [
        'is_important' => 'boolean',
        'is_read' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(Users_tbl::class, 'user_id');
    }
}
