<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification_settings_tbl extends Model
{
    protected $table = 'notification_settings_tbls';

    protected $fillable = [
        'user_id',
        'mute_inbox',
        'mute_spam',
        'mute_social',
    ];

    protected $casts = [
        'mute_inbox' => 'boolean',
        'mute_spam' => 'boolean',
        'mute_social' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(Users_tbl::class, 'user_id');
    }
}
