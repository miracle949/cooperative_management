<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class committee_tbl extends Model
{
    public $incrementing = true;

    protected $table = 'committees_tbls';

    protected $fillable = [
        'name',
        'description',
        'color',
        'sort_order',
    ];

    public function members()
    {
        return $this->hasMany(committee_member_tbl::class, 'committee_id');
    }

    public function users()
    {
        return $this->belongsToMany(Users_tbl::class, 'committee_members_tbls', 'committee_id', 'user_id')
            ->withPivot('is_chair');
    }
}
