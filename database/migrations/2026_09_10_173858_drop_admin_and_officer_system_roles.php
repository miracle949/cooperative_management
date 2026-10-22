<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Remove the legacy 'admin' and 'officer' system roles. The General Manager
     * already carries main-admin authority (isMainAdmin() returns true for the
     * GM), so 'admin' is consolidated into 'general-manager'.
     */
    public function up(): void
    {
        DB::table('users_tbls')
            ->whereIn(DB::raw('LOWER(role)'), ['admin', 'officer'])
            ->update(['role' => 'general-manager']);

        DB::table('roles')
            ->whereIn('slug', ['admin', 'officer'])
            ->where('is_system', true)
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $now = now();

        foreach ([
            ['name' => 'Admin', 'slug' => 'admin', 'description' => 'Full system access'],
            ['name' => 'Officer', 'slug' => 'officer', 'description' => 'Limited sidebar access'],
        ] as $role) {
            if (! DB::table('roles')->where('slug', $role['slug'])->exists()) {
                DB::table('roles')->insert($role + [
                    'is_system' => true,
                    'sidebar_permissions' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
};