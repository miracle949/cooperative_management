<?php
// app/Models/SupportTicket_tbl.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportTicket_tbl extends Model
{
    protected $table = 'support_tickets_tbls';

    protected $fillable = [
        'user_id',
        'category',
        'subject',
        'message',
        'proof_path',
        'status',
        'admin_response',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(Users_tbl::class, 'user_id');
    }
}