<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountSettings_tbl extends Model
{
    protected $table = 'account_settings_tbls';

    protected $fillable = [
        'user_id',
        'loan_reminders',
        'savings_updates',
        'email_digest',
        'announcements',
        'login_alerts',
    ];

    protected $casts = [
        'loan_reminders' => 'boolean',
        'savings_updates' => 'boolean',
        'email_digest' => 'boolean',
        'announcements' => 'boolean',
        'login_alerts' => 'boolean',
    ];
}
