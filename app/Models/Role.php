<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    /**
     * Maximum number of custom (non-system) roles that can exist at once.
     * System roles (General Manager) do not count toward this limit.
     */
    public const MAX_CUSTOM_ROLES = 5;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_system',
        'sidebar_permissions',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'sidebar_permissions' => 'array',
    ];
}
