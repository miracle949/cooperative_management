<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Otherinfo_tbl extends Model
{
    protected $table = 'otherinfo_tbls';

    protected $fillable = [
        'user_id',
        'membership_category',
        'email_verified',
        'place_of_birth',
        'date_of_birth',
        'contact_no',
        'present_address',
        'permanent_address',
        'sex',
        'civil_status',
        'citizenship',
        'height',
        'weight',
        'blood_type',
        'skills',
        'profile_picture',
        'signature',
        'approval_status',
        'membership_status',
    ];
}
