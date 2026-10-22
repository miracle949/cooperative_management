<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $table = 'audit_logs';

    protected $fillable = [
        'user_id',
        'admin_name',
        'ip_address',
        'user_role',
        'action',
        'details',
        'target_type',
        'target_id',
    ];

    public function user()
    {
        return $this->belongsTo(Users_tbl::class, 'user_id');
    }

    public static function log($action, $details = null, $targetType = null, $targetId = null, $ipAddress = null)
    {
        $admin = auth()->user();
        $ip = $ipAddress ?? request()->ip();

        return self::create([
            'user_id' => $admin?->id,
            'admin_name' => $admin ? trim($admin->first_name.' '.$admin->last_name) : 'System',
            'ip_address' => $ip,
            'user_role' => $admin?->role,
            'action' => $action,
            'details' => $details,
            'target_type' => $targetType,
            'target_id' => $targetId,
        ]);
    }
}
