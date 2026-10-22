<?php

use App\Models\Users_tbl;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// ────────────────────────────────────────────────────────────────────────────
// Audit Logs are General Manager-only: sidebar link, dashboard card, and the
// /admin/audit-logs route are all restricted to the GM.
// ────────────────────────────────────────────────────────────────────────────

function auditAuthTables(int $customRoles = 0): void
{
    Schema::create('users_tbls', function (Blueprint $t) {
        $t->id();
        $t->string('first_name')->nullable();
        $t->string('last_name')->nullable();
        $t->string('email')->nullable();
        $t->string('username')->nullable();
        $t->string('password');
        $t->string('role')->nullable();
        $t->string('base_role')->nullable();
        $t->string('status')->nullable();
        $t->text('sidebar_permissions')->nullable();
        $t->timestamps();
    });

    Schema::create('roles', function (Blueprint $t) {
        $t->id();
        $t->string('name');
        $t->string('slug')->unique();
        $t->string('description')->nullable();
        $t->boolean('is_system')->default(false);
        $t->text('sidebar_permissions')->nullable();
        $t->timestamps();
    });

    Schema::create('audit_logs', function (Blueprint $t) {
        $t->id();
        $t->unsignedBigInteger('user_id')->nullable();
        $t->string('admin_name')->nullable();
        $t->string('ip_address')->nullable();
        $t->string('user_role')->nullable();
        $t->string('action');
        $t->text('details')->nullable();
        $t->string('target_type')->nullable();
        $t->unsignedBigInteger('target_id')->nullable();
        $t->timestamps();
    });

    for ($i = 1; $i <= $customRoles; $i++) {
        DB::table('roles')->insert([
            'name' => "Custom $i",
            'slug' => "custom-$i",
            'description' => 'Test role',
            'is_system' => false,
            'sidebar_permissions' => json_encode(['dashboard']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

function auditAuthUser(array $attrs = []): Users_tbl
{
    $row = array_merge([
        'first_name' => 'Ronald',
        'last_name' => 'Sales',
        'username' => 'ronald',
        'email' => 'ronald@coop.com',
        'password' => bcrypt('password'),
        'role' => 'general-manager',
        'base_role' => null,
        'status' => 'active',
    ], $attrs);

    return Users_tbl::create($row);
}

it('lets the General Manager view the audit logs page', function () {
    auditAuthTables();

    $gm = auditAuthUser();

    $this->actingAs($gm)->get('/admin/audit-logs')->assertOk();
});

it('blocks custom-role staff from viewing the audit logs page', function () {
    auditAuthTables(1);

    $secretary = auditAuthUser([
        'first_name' => 'Bear',
        'last_name' => 'Brand',
        'username' => 'bear',
        'email' => 'bear@example.com',
        'role' => 'secretary',
        'base_role' => 'member',
    ]);

    $this->actingAs($secretary)->get('/admin/audit-logs')->assertForbidden();
});

it('blocks member-based accounts from viewing the audit logs page', function () {
    auditAuthTables();

    $member = auditAuthUser([
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'username' => 'jane',
        'email' => 'jane@example.com',
        'role' => 'member',
        'base_role' => null,
    ]);

    $this->actingAs($member)->get('/admin/audit-logs')->assertForbidden();
});

it('gates the audit logs sidebar link to the General Manager only', function () {
    $layout = file_get_contents(base_path('resources/views/layouts/admin.blade.php'));

    $auditLinkBlock = '#@if\(\$user\?->isGeneralManager\(\)\).*?Audit Logs.*?@endif#s';
    expect(preg_match($auditLinkBlock, $layout))->toBe(1);

    $roleBlock = '#@if\(\$hasFullAccess \|\| in_array\([\'"]audit-logs[\'"]#s';
    expect(preg_match($roleBlock, $layout))->toBe(0);
});

it('hides the dashboard audit logs card for non-General Managers', function () {
    $dashboard = file_get_contents(base_path('resources/views/admin_components/dashboard.blade.php'));

    expect($dashboard)->toContain('$showAuditLogs = auth()->user()?->isGeneralManager()')
        ->and($dashboard)->toContain('@if($showAuditLogs)');
});

it('no longer offers Audit Logs as a grantable custom-role permission', function () {
    $allied = file_get_contents(base_path('resources/views/admin_components/allied_workers.blade.php'));

    expect($allied)->not->toContain("'audit-logs'");
});