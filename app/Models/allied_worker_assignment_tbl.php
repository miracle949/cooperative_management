<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class allied_worker_assignment_tbl extends Model
{
    public $incrementing = true;

    protected $table = 'allied_worker_assignments';

    protected $fillable = [
        'user_id',
        'previous_role',
        'previous_membership_category',
        'new_role',
        'assigned_by',
        'assigned_at',
        'id_document_path',
        'status',
        'revoked_by',
        'revoked_at',
        'reason',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(Users_tbl::class, 'user_id');
    }

    public function assigner()
    {
        return $this->belongsTo(Users_tbl::class, 'assigned_by');
    }

    public function revoker()
    {
        return $this->belongsTo(Users_tbl::class, 'revoked_by');
    }
}
