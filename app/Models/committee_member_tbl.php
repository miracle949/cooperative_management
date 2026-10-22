<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class committee_member_tbl extends Model
{
    public $incrementing = true;

    protected $table = 'committee_members_tbls';

    protected $fillable = [
        'committee_id',
        'user_id',
        'is_chair',
    ];

    protected $casts = [
        'is_chair' => 'boolean',
    ];

    public function committee()
    {
        return $this->belongsTo(committee_tbl::class, 'committee_id');
    }

    public function user()
    {
        return $this->belongsTo(Users_tbl::class, 'user_id');
    }
}
