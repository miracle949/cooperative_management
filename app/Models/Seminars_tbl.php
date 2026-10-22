<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seminars_tbl extends Model
{
    protected $table = 'seminars_tbls';

    protected $fillable = [
        'seminar_type',
        'schedule_datetime',
        'delivery_type',
        'online_link',
        'meetup_place',
        'exact_venue',
    ];

    protected $casts = [
        'schedule_datetime' => 'datetime',
    ];

    public function attendees()
    {
        return $this->hasMany(SeminarAttendees_tbl::class, 'seminar_id');
    }

    public function attendingUsers()
    {
        return $this->belongsToMany(Users_tbl::class, 'seminar_attendees_tbls', 'seminar_id', 'user_id')
            ->withPivot('status')
            ->withTimestamps();
    }
}
