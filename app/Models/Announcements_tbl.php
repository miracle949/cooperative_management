<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcements_tbl extends Model
{
    protected $table = 'announcements_tbls';

    protected $fillable = [
        'user_id',
        'title',
        'content',
    ];

    public function user()
    {
        return $this->belongsTo(Users_tbl::class, 'user_id');
    }

    public function comments()
    {
        return $this->hasMany(AnnouncementComments_tbl::class, 'announcement_id');
    }

    public function likes()
    {
        return $this->hasMany(AnnouncementLikes_tbl::class, 'announcement_id');
    }
}
