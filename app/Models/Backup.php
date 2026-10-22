<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Backup extends Model
{
    protected $table = 'backups';

    protected $fillable = [
        'filename',
        'disk_path',
        'size_bytes',
        'created_by',
    ];

    protected $casts = [
        'size_bytes' => 'integer',
    ];

    public function creator()
    {
        return $this->belongsTo(Users_tbl::class, 'created_by');
    }

    public function getSizeFormattedAttribute(): string
    {
        return round($this->size_bytes / 1024 / 1024, 2) . ' MB';
    }

    public function getFullPathAttribute(): string
    {
        return storage_path('app/' . $this->disk_path);
    }
}