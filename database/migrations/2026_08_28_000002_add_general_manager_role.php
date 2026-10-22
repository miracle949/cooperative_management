<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // General Manager: the authority that resolves GM-review exceptions and
        // can manage Allied Workers.
        $exists = DB::table('roles')->where('slug', 'general-manager')->exists();
        if (! $exists) {
            DB::table('roles')->insert([
                'name' => 'General Manager',
                'slug' => 'general-manager',
                'description' => 'Full authority including GM review and Allied Worker management',
                'is_system' => true,
                'sidebar_permissions' => json_encode([
                    'dashboard', 'members', 'savings', 'sharecapitals', 'lendings',
                    'payments', 'finance', 'reports', 'seminars',
                    'officers-committees', 'settings',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('roles')->where('slug', 'general-manager')->delete();
    }
};
