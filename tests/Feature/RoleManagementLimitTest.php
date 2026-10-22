<?php

use App\Models\Role;
use App\Models\Users_tbl;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// ────────────────────────────────────────────────────────────────────────────
// Custom role cap
// ────────────────────────────────────────────────────────────────────────────

function roleLimitTables(): void
{
    Schema::create('users_tbls', function (Blueprint $t) {
        $t->id();
        $t->string('first_name');
        $t->string('last_name');
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
}

function roleLimitUser(string $role = 'general-manager'): Users_tbl
{
    return Users_tbl::create([
        'first_name' => 'Ronald',
        'last_name' => 'Sales',
        'username' => 'ronald',
        'email' => 'ronald@coop.com',
        'password' => bcrypt('password'),
        'role' => $role,
        'base_role' => null,
        'status' => 'active',
    ]);
}

function seedCustomRoles(int $count): void
{
    for ($i = 1; $i <= $count; $i++) {
        DB::table('roles')->insert([
            'name' => "Custom $i",
            'slug' => "custom-$i",
            'description' => 'Test role',
            'is_system' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

it('blocks creating a custom role once the cap of 5 is reached', function () {
    roleLimitTables();

    $gm = roleLimitUser();

    seedCustomRoles(Role::MAX_CUSTOM_ROLES);

    $response = $this->actingAs($gm)->postJson('/roles/store', [
        'name' => 'Overflow',
        'description' => 'Should be rejected',
        'sidebar_permissions' => ['dashboard'],
    ]);

    $response->assertStatus(422)
        ->assertJson(['success' => false]);

    expect(DB::table('roles')->where('is_system', false)->count())->toBe(Role::MAX_CUSTOM_ROLES);
});

it('does not count the General Manager (system role) toward the custom role cap', function () {
    roleLimitTables();

    $gm = roleLimitUser();

    DB::table('roles')->insert([
        'name' => 'General Manager',
        'slug' => 'general-manager',
        'description' => 'System',
        'is_system' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    seedCustomRoles(Role::MAX_CUSTOM_ROLES);

    $response = $this->actingAs($gm)->postJson('/roles/store', [
        'name' => 'Overflow',
        'description' => 'Rejected even though a system role exists',
        'sidebar_permissions' => [],
    ]);

    $response->assertStatus(422)
        ->assertJson(['success' => false]);
});

it('allows creating custom roles while under the cap', function () {
    roleLimitTables();

    $gm = roleLimitUser();

    $response = $this->actingAs($gm)->postJson('/roles/store', [
        'name' => 'Staff',
        'description' => 'A custom role',
        'sidebar_permissions' => ['dashboard', 'members'],
    ]);

    $response->assertJson(['success' => true, 'role' => ['slug' => 'staff']]);

    expect(DB::table('roles')->where('slug', 'staff')->exists())->toBeTrue();
});