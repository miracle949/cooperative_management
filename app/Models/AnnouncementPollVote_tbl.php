<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnnouncementPollVote_tbl extends Model
{
    protected $table = 'announcement_poll_votes_tbls';

    protected $fillable = [
        'poll_id',
        'user_id',
        'option_index',
    ];

    public function poll()
    {
        return $this->belongsTo(AnnouncementPoll_tbl::class, 'poll_id');
    }

    public function user()
    {
        return $this->belongsTo(Users_tbl::class, 'user_id');
    }
}
