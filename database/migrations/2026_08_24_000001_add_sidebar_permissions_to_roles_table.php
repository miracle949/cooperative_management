<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->json('sidebar_permissions')->nullable()->after('is_system');
        });

        $validKeys = [
            'dashboard', 'members', 'savings', 'sharecapitals', 'lendings',
            'payments', 'finance', 'reports', 'seminars',
            'officers-committees', 'settings',
        ];

        $roles = DB::table('roles')->get();
        $users = DB::table('users_tbls')->whereNotNull('sidebar_permissions')->get();

        $rolePerms = [];
        foreach ($users as $user) {
            $slug = $user->role;
            if (! isset($rolePerms[$slug])) {
                $rolePerms[$slug] = [];
            }
            $perms = json_decode($user->sidebar_permissions, true) ?? [];
            foreach ($perms as $p) {
                if (in_array($p, $validKeys)) {
                    $rolePerms[$slug][] = $p;
                }
            }
        }

        foreach ($roles as $role) {
            if ($role->slug === 'admin') {
                DB::table('roles')->where('id', $role->id)->update(['sidebar_permissions' => null]);
            } elseif (isset($rolePerms[$role->slug])) {
                $merged = array_values(array_unique($rolePerms[$role->slug]));
                DB::table('roles')->where('id', $role->id)->update([
                    'sidebar_permissions' => json_encode($merged),
                ]);
            } else {
                DB::table('roles')->where('id', $role->id)->update(['sidebar_permissions' => null]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('sidebar_permissions');
        });
    }
};
