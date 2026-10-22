<?php

use App\Models\share_capital_account_tbl;
use App\Models\share_capital_transaction_tbl;
use App\Models\Users_tbl;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// ────────────────────────────────────────────────────────────────────────────
// Inactive accounts get a dedicated read-only page, a backend-enforced
// single reactivation request, and a narrow admin approve/reject flow that
// never touches financial records.
//
// Invariants under test:
//   1. The inactive page only READS share capital (ledger-backed) — the view
//      must never create, modify or delete any financial row.
//   2. A request always uses Auth::id() — no client-supplied member ID.
//   3. Status transitions are exact:
//        inactive →(Request)→ reactivation_pending
//        reactivation_pending →(Admin Approve)→ active
//        reactivation_pending →(Admin Reject)→ inactive
//   4. A member with a request already under review can't submit again.
//   5. approReactivation only restores status/role/membership and the SC
//      account status (never Closed); reject just sets status back.
//   6. The member.active middleware blocks inactive accounts from every
//      member area by redirecting to the inactive page.
// ────────────────────────────────────────────────────────────────────────────

function inactiveTables(): void
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

    Schema::create('otherinfo_tbls', function (Blueprint $t) {
        $t->id();
        $t->unsignedBigInteger('user_id');
        $t->string('membership_category')->nullable();
        $t->string('membership_status')->nullable();
        $t->string('approval_status')->nullable();
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

    Schema::create('share_capital_account_tbls', function (Blueprint $t) {
        $t->id();
        $t->unsignedBigInteger('user_id');
        $t->decimal('total_amount', 12, 2)->default(0);
        $t->string('status')->default('active');
        $t->date('opened_at')->nullable();
        $t->timestamps();
    });

    Schema::create('share_capital_transaction_tbls', function (Blueprint $t) {
        $t->id();
        $t->unsignedBigInteger('user_id')->nullable();
        $t->unsignedBigInteger('share_capital_account_id')->nullable();
        $t->unsignedBigInteger('account_id')->nullable();
        $t->string('type')->nullable();
        $t->decimal('shares', 12, 2)->default(0);
        $t->decimal('total_amount', 12, 2)->default(0);
        $t->string('status')->nullable();
        $t->string('payment_method')->nullable();
        $t->string('reference_no')->nullable();
        $t->timestamp('transaction_date')->nullable();
        $t->timestamps();
    });
}

function inactiveMember(array $attrs = []): Users_tbl
{
    $row = array_merge([
        'first_name' => 'Mia',
        'last_name' => 'Cruz',
        'username' => 'miacruz',
        'email' => 'mia@example.com',
        'password' => bcrypt('password'),
        'role' => 'member',
        'base_role' => null,
        'status' => 'inactive',
    ], $attrs);

    $user = Users_tbl::create($row);

    DB::table('otherinfo_tbls')->insert([
        'user_id' => $user->id,
        'membership_category' => 'Regular',
        'membership_status' => 'Inactive',
        'approval_status' => 'Approved',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return $user;
}

function scAccountFor(int $userId, string $status = 'Inactive', float $amount = 5000.0): share_capital_account_tbl
{
    return share_capital_account_tbl::create([
        'user_id' => $userId,
        'total_amount' => $amount,
        'status' => $status,
    ]);
}

function scDeposits(int $userId, int $accountId, float $amount = 5000.0, float $shares = 50.0): void
{
    share_capital_transaction_tbl::create([
        'user_id' => $userId,
        'share_capital_account_id' => $accountId,
        'type' => 'Deposit',
        'shares' => $shares,
        'total_amount' => $amount,
        'status' => 'Completed',
        'transaction_date' => now(),
    ]);
}

function financialSnapshot(): array
{
    return [
        'sc_accounts' => DB::table('share_capital_account_tbls')->count(),
        'sc_transactions' => DB::table('share_capital_transaction_tbls')->count(),
    ];
}

function reachableMemberAreas(): array
{
    return ['/member-portal', '/savings-page', '/Financial', '/loan-status', '/transactions', '/profile-member', '/settings'];
}

it('lets an inactive member view the read-only reactivation page', function () {
    inactiveTables();

    $member = inactiveMember();
    scAccountFor($member->id);

    $this->actingAs($member)->get('/member/inactive')->assertOk();
});

it('shows the ledger-backed share capital balance on the inactive page', function () {
    inactiveTables();

    $member = inactiveMember();
    $account = scAccountFor($member->id);
    scDeposits($member->id, $account->id, 7500.75, 75);

    $response = $this->actingAs($member)->get('/member/inactive');
    $response->assertOk()
        ->assertSee('7,500.75')
        ->assertSee('75');
});

it('shows "No remaining share capital" when the account is Closed or has no balance', function () {
    inactiveTables();

    $member = inactiveMember();
    scAccountFor($member->id, 'Closed', 0.0);

    // Closed account → nothing rendered, even though a row exists.
    $this->actingAs($member)->get('/member/inactive')
        ->assertOk()
        ->assertSee('No remaining share capital');

    // No account at all → the page must NOT create one.
    $member2 = inactiveMember(['email' => 'no-sc@example.com']);
    $this->actingAs($member2)->get('/member/inactive')
        ->assertOk()
        ->assertSee('No remaining share capital');

    expect(DB::table('share_capital_account_tbls')->count())->toBe(1);
});

it('never creates or modifies financial rows when viewing the inactive page', function () {
    inactiveTables();

    $member = inactiveMember();
    $account = scAccountFor($member->id);
    scDeposits($member->id, $account->id);

    $before = financialSnapshot();
    $this->actingAs($member)->get('/member/inactive')->assertOk();
    $after = financialSnapshot();

    expect($after)->toBe($before);
    expect($member->fresh()->status)->toBe('inactive');
    expect($account->fresh()->status)->toBe('Inactive');
    expect(DB::table('share_capital_transaction_tbls')->count())->toBe(1);
});

it('submitting a reactivation request moves the account to reactivation_pending only', function () {
    inactiveTables();

    $member = inactiveMember();

    $this->actingAs($member)->post('/member/reactivate')
        ->assertRedirect(route('login'));

    expect(Users_tbl::find($member->id)->status)->toBe('reactivation_pending');
    expect(DB::table('audit_logs')->where('action', 'Reactivation Requested')->where('target_id', $member->id)->exists())->toBeTrue();
});

it('rejects a duplicate reactivation request while still under review', function () {
    inactiveTables();

    $member = inactiveMember();
    $member->update(['status' => 'reactivation_pending']);

    $this->actingAs($member)->post('/member/reactivate')
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('login');

    expect(Users_tbl::find($member->id)->status)->toBe('reactivation_pending');
    expect(DB::table('audit_logs')->where('action', 'Reactivation Requested')->count())->toBe(0);
});

it('keeps requests isolated per authenticated member (Auth::id only)', function () {
    inactiveTables();

    $mia = inactiveMember();
    $leo = inactiveMember(['email' => 'leo@example.com', 'first_name' => 'Leo', 'last_name' => 'Reyes']);

    $this->actingAs($mia)->post('/member/reactivate')->assertRedirect(route('login'));

    expect($mia->fresh()->status)->toBe('reactivation_pending');
    expect($leo->fresh()->status)->toBe('inactive');

    $this->actingAs($leo)->post('/member/reactivate')->assertRedirect(route('login'));

    expect($leo->fresh()->status)->toBe('reactivation_pending');
    expect(DB::table('audit_logs')->where('action', 'Reactivation Requested')->count())->toBe(2);
});

it('a reactivation request never touches financial records', function () {
    inactiveTables();

    $member = inactiveMember();
    $account = scAccountFor($member->id);
    scDeposits($member->id, $account->id);

    $before = financialSnapshot();
    $this->actingAs($member)->post('/member/reactivate')->assertRedirect(route('login'));

    expect(DB::table('share_capital_transaction_tbls')->count())->toBe(1);
    expect($account->fresh()->status)->toBe('Inactive');
    expect(financialSnapshot())->toBe($before);
});

it('an admin approving a reactivation restores the member without financial changes', function () {
    inactiveTables();

    $member = inactiveMember();
    $member->update(['status' => 'reactivation_pending']);

    $account = scAccountFor($member->id);
    scDeposits($member->id, $account->id);

    $admin = inactiveMember([
        'email' => 'gm@example.com',
        'first_name' => 'GM',
        'last_name' => 'Admin',
        'role' => 'general-manager',
    ]);

    $before = financialSnapshot();
    $this->actingAs($admin)->post("/admin/member/{$member->id}/reactivate")
        ->assertRedirect()
        ->assertSessionHas('success');

    $member = $member->fresh();
    expect($member->status)->toBe('active');
    expect($member->role)->toBe('member');
    expect(DB::table('otherinfo_tbls')->where('user_id', $member->id)->value('membership_status'))->toBe('Active');
    expect($account->fresh()->status)->toBe('Active');
    expect(financialSnapshot())->toBe($before);
    expect(DB::table('audit_logs')->where('action', 'Approved Reactivation')->where('target_id', $member->id)->exists())->toBeTrue();
});

it('an approving reactivation never reopens a Closed share capital account', function () {
    inactiveTables();

    $member = inactiveMember();
    $member->update(['status' => 'reactivation_pending']);

    $account = scAccountFor($member->id, 'Closed', 0.0);

    $admin = inactiveMember([
        'email' => 'gm2@example.com',
        'first_name' => 'GM',
        'last_name' => 'Two',
        'role' => 'general-manager',
    ]);

    $this->actingAs($admin)->post("/admin/member/{$member->id}/reactivate")
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($member->fresh()->status)->toBe('active');
    expect($account->fresh()->status)->toBe('Closed');
});

it('an admin rejecting a reactivation returns the member to inactive', function () {
    inactiveTables();

    $member = inactiveMember();
    $member->update(['status' => 'reactivation_pending']);

    $admin = inactiveMember([
        'email' => 'gm3@example.com',
        'first_name' => 'GM',
        'last_name' => 'Three',
        'role' => 'general-manager',
    ]);

    $this->actingAs($admin)->post("/admin/member/{$member->id}/reactivate/reject")
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($member->fresh()->status)->toBe('inactive');
    expect(Users_tbl::where('id', $member->id)->exists())->toBeTrue();
    expect(DB::table('audit_logs')->where('action', 'Rejected Reactivation')->where('target_id', $member->id)->exists())->toBeTrue();
});

it('blocks reactivating a member who has no pending request', function () {
    inactiveTables();

    $member = inactiveMember();

    $admin = inactiveMember([
        'email' => 'gm4@example.com',
        'first_name' => 'GM',
        'last_name' => 'Four',
        'role' => 'general-manager',
    ]);

    $this->actingAs($admin)->post("/admin/member/{$member->id}/reactivate")
        ->assertRedirect()
        ->assertSessionHas('error');

    expect($member->fresh()->status)->toBe('inactive');
});

it('redirects inactive members away from every member area to the inactive page', function () {
    inactiveTables();

    $member = inactiveMember();

    foreach (reachableMemberAreas() as $url) {
        $this->actingAs($member)->get($url)
            ->assertRedirect(route('member.inactive'));
    }
});

it('redirects active members away from the inactive page to the member portal', function () {
    inactiveTables();

    $member = inactiveMember(['status' => 'active']);

    $this->actingAs($member)->get('/member/inactive')
        ->assertRedirect(route('MemberPortal'));
});

it('sends reactivation_pending members back to the under-review page instead of member areas', function () {
    inactiveTables();

    $member = inactiveMember();
    $member->update(['status' => 'reactivation_pending']);

    foreach (reachableMemberAreas() as $url) {
        $this->actingAs($member)->get($url)
            ->assertRedirect(route('member.inactive'));
    }
});

it('logs in an inactive member and lands them on the reactivation page', function () {
    inactiveTables();

    inactiveMember();

    $this->post('/login-handle', ['login' => 'mia@example.com', 'password' => 'password'])
        ->assertRedirect(route('member.inactive'));

    expect(auth()->check())->toBeTrue();
});

it('logins for reactivation_pending members are held under review', function () {
    inactiveTables();

    $member = inactiveMember();
    $member->update(['status' => 'reactivation_pending']);

    $this->post('/login-handle', ['login' => 'mia@example.com', 'password' => 'password'])
        ->assertRedirect()
        ->assertSessionHasErrors('login');

    expect(auth()->check())->toBeFalse();
});