<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeminarAttendees_tbl extends Model
{
    protected $table = 'seminar_attendees_tbls';

    protected $fillable = [
        'seminar_id',
        'user_id',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function seminar()
    {
        return $this->belongsTo(Seminars_tbl::class, 'seminar_id');
    }

    public function user()
    {
        return $this->belongsTo(Users_tbl::class, 'user_id');
    }
}
