<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnnouncementPoll_tbl extends Model
{
    protected $table = 'announcement_polls_tbls';

    protected $fillable = [
        'user_id',
        'question',
        'options',
        'expires_at',
    ];

    protected $casts = [
        'options' => 'array',
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(Users_tbl::class, 'user_id');
    }

    public function votes()
    {
        return $this->hasMany(AnnouncementPollVote_tbl::class, 'poll_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function getResultsAttribute(): array
    {
        $options = $this->options ?? [];
        $results = array_fill(0, count($options), 0);

        foreach ($this->votes as $vote) {
            if (isset($results[$vote->option_index])) {
                $results[$vote->option_index]++;
            }
        }

        return $results;
    }
}
