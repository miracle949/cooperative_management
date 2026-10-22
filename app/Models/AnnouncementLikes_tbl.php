<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnnouncementLikes_tbl extends Model
{
    protected $table = 'announcement_likes_tbls';

    protected $fillable = [
        'announcement_id',
        'user_id',
    ];

    public function announcement()
    {
        return $this->belongsTo(Announcements_tbl::class, 'announcement_id');
    }

    public function user()
    {
        return $this->belongsTo(Users_tbl::class, 'user_id');
    }
}
