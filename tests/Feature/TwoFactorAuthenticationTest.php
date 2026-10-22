<?php

use App\Models\AuditLog;
use App\Models\Users_tbl;
use App\Models\two_factor_secret_tbl;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Schema;
use OTPHP\TOTP;

// NOTE: phpunit.xml runs against sqlite :memory: and RefreshDatabase is
// disabled in tests/Pest.php, so every table a test touches must be created
// by hand here (drop-if-exists keeps each test isolated). Global helper/const
// names are unique across test files to avoid redeclaration collisions.

function twoFactorTables(): void
{
    Schema::dropIfExists('two_factor_secrets_tbls');
    Schema::create('two_factor_secrets_tbls', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id')->unique();
        $table->text('secret')->nullable();
        $table->text('recovery_codes')->nullable();
        $table->timestamp('confirmed_at')->nullable();
        $table->boolean('enabled')->default(false);
        $table->timestamps();
    });

    Schema::dropIfExists('users_tbls');
    Schema::create('users_tbls', function (Blueprint $table) {
        $table->id();
        $table->string('first_name')->nullable();
        $table->string('last_name')->nullable();
        $table->string('username')->nullable();
        $table->string('email')->nullable();
        $table->string('password')->nullable();
        $table->string('role')->nullable();
        $table->string('base_role')->nullable();
        $table->string('status')->nullable();
        $table->timestamp('password_changed_at')->nullable();
        $table->timestamps();
    });

    Schema::dropIfExists('roles');
    Schema::create('roles', function (Blueprint $table) {
        $table->id();
        $table->string('slug')->nullable();
        $table->string('name')->nullable();
        $table->text('sidebar_permissions')->nullable();
        $table->timestamps();
    });

    Schema::dropIfExists('audit_logs');
    Schema::create('audit_logs', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id')->nullable();
        $table->string('admin_name')->nullable();
        $table->string('ip_address')->nullable();
        $table->string('user_role')->nullable();
        $table->string('action');
        $table->text('details')->nullable();
        $table->string('target_type')->nullable();
        $table->unsignedBigInteger('target_id')->nullable();
        $table->timestamps();
    });

    Schema::dropIfExists('otherinfo_tbls');
    Schema::create('otherinfo_tbls', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id')->nullable();
        $table->string('approval_status')->nullable();
        $table->timestamps();
    });

    Schema::dropIfExists('password_reset_tokens');
    Schema::create('password_reset_tokens', function (Blueprint $table) {
        $table->string('email')->primary();
        $table->string('token');
        $table->timestamp('created_at')->nullable();
    });
}

function twoFactorUser(string $role, ?string $baseRole = null, string $status = 'Active'): Users_tbl
{
    $user = Users_tbl::create([
        'first_name' => ucfirst($role),
        'last_name' => 'User',
        'username' => strtolower($role),
        'email' => strtolower($role) . '@kpmpcats.test',
        'password' => bcrypt('Passw0rd!'),
        'role' => $role,
        'base_role' => $baseRole,
        'status' => $status,
        'password_changed_at' => now(),
    ]);

    DB::table('otherinfo_tbls')->insertOrIgnore([
        'user_id' => $user->id,
        'approval_status' => 'Approved',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return $user;
}

function twoFactorCode(string $secret, ?int $timestamp = null): string
{
    return TOTP::createFromSecret($secret)->at($timestamp ?? time());
}

/**
 * Enables 2FA for a user as if they had completed enrollment.
 * Returns the plaintext secret and recovery codes for assertions.
 */
function enableTwoFactorFor(Users_tbl $user, ?string $forcedSecret = null): array
{
    $secret = $forcedSecret ?? TOTP::generate()->getSecret();

    $codes = [];
    while (count($codes) < 10) {
        $code = strtoupper(bin2hex(random_bytes(10)));
        $codes[] = implode('-', str_split($code, 4));
    }

    two_factor_secret_tbl::create([
        'user_id' => $user->id,
        'secret' => $secret,
        'recovery_codes' => array_map(static fn (string $code): string => Hash::make($code), $codes),
        'confirmed_at' => now(),
        'enabled' => true,
    ]);

    return ['secret' => $secret, 'codes' => $codes];
}

function signIn(Users_tbl $user): void
{
    test()->withSession(['2fa.verified' => true])->actingAs($user);
}

// ────────────────────────────────────────────────────────────────────────────
// Enrollment
// ────────────────────────────────────────────────────────────────────────────

it('lets an eligible privileged user begin enrollment', function () {
    twoFactorTables();
    $gm = twoFactorUser('general-manager');
    signIn($gm);

    $this->get(route('2fa.manage'))->assertOk()->assertSee('2FA is not enabled');

    $this->post(route('2fa.enroll'))->assertRedirect(route('2fa.manage'));

    $record = two_factor_secret_tbl::where('user_id', $gm->id)->first();
    expect($record)->not->toBeNull()
        ->and((bool) $record->enabled)->toBeFalse()
        ->and($record->confirmed_at)->toBeNull()
        ->and($record->secret)->not->toBeNull();

    expect(DB::table('audit_logs')
        ->where('action', '2FA Enrollment Initiated')
        ->where('target_type', 'user')
        ->where('target_id', $gm->id)
        ->exists())->toBeTrue();

    // The manage page now shows the setup QR and the manual secret.
    $page = $this->get(route('2fa.manage'))->assertOk();
    $page->assertSee('<svg', false)->assertSee($record->secret);
});

it('stores the TOTP secret encrypted, never in plaintext', function () {
    twoFactorTables();
    $gm = twoFactorUser('general-manager');

    $material = enableTwoFactorFor($gm);

    $raw = DB::table('two_factor_secrets_tbls')->where('user_id', $gm->id)->value('secret');
    expect($raw)->not->toBe($material['secret'])
        ->and(str_contains((string) $raw, $material['secret']))->toBeFalse();

    $model = two_factor_secret_tbl::where('user_id', $gm->id)->first();
    expect($model->secret)->toBe($material['secret']);
});

it('generates recovery codes at confirmation and never stores them in plaintext', function () {
    twoFactorTables();
    $gm = twoFactorUser('general-manager');
    signIn($gm);

    $this->post(route('2fa.enroll'))->assertRedirect(route('2fa.manage'));
    $record = two_factor_secret_tbl::where('user_id', $gm->id)->first();

    $this->post(route('2fa.confirm'), ['code' => twoFactorCode($record->secret)])
        ->assertRedirect(route('2fa.manage'));

    $fresh = $record->fresh();
    expect((bool) $fresh->enabled)->toBeTrue()
        ->and($fresh->confirmed_at)->not->toBeNull()
        ->and($fresh->recovery_codes)->toBeArray()
        ->and(count($fresh->recovery_codes))->toBe(10);

    $rawRecovery = DB::table('two_factor_secrets_tbls')->where('user_id', $gm->id)->value('recovery_codes');
    foreach ($fresh->recovery_codes as $hash) {
        expect(str_contains((string) $rawRecovery, $hash))->toBeFalse();
    }
});

it('keeps 2FA disabled until a valid code confirms enrollment', function () {
    twoFactorTables();
    $gm = twoFactorUser('general-manager');
    signIn($gm);

    $this->post(route('2fa.enroll'))->assertRedirect(route('2fa.manage'));
    $record = two_factor_secret_tbl::where('user_id', $gm->id)->first();

    $this->post(route('2fa.confirm'), ['code' => '123456'])
        ->assertSessionHasErrors('code');

    $fresh = $record->fresh();
    expect((bool) $fresh->enabled)->toBeFalse()
        ->and($fresh->confirmed_at)->toBeNull()
        ->and($fresh->recovery_codes)->toBeNull()
        ->and($fresh->secret)->not->toBeNull();
});

it('confirms enrollment with a valid TOTP code and enables 2FA', function () {
    twoFactorTables();
    $gm = twoFactorUser('general-manager');
    signIn($gm);

    $this->post(route('2fa.enroll'))->assertRedirect(route('2fa.manage'));
    $record = two_factor_secret_tbl::where('user_id', $gm->id)->first();

    $this->post(route('2fa.confirm'), ['code' => twoFactorCode($record->secret)])
        ->assertRedirect(route('2fa.manage'))
        ->assertSessionHas('status');

    $fresh = $record->fresh();
    expect((bool) $fresh->enabled)->toBeTrue()
        ->and($fresh->confirmed_at)->not->toBeNull();

    expect($this->app['session']->get('2fa.verified'))->toBeTrue();

    expect(DB::table('audit_logs')
        ->where('action', '2FA Enabled')
        ->where('target_id', $gm->id)
        ->exists())->toBeTrue();
});

it('shows recovery codes exactly once during enrollment', function () {
    twoFactorTables();
    $gm = twoFactorUser('general-manager');
    signIn($gm);

    $this->post(route('2fa.enroll'))->assertRedirect(route('2fa.manage'));
    $record = two_factor_secret_tbl::where('user_id', $gm->id)->first();

    $this->post(route('2fa.confirm'), ['code' => twoFactorCode($record->secret)]);

    $plaintextCodes = $this->app['session']->get('2fa.new-recovery-codes');
    expect($plaintextCodes)->toBeArray()
        ->and(count($plaintextCodes))->toBe(10);

    // First management render after confirmation displays the codes.
    $first = $this->get(route('2fa.manage'))->assertOk();
    $first->assertSee($plaintextCodes[0])->assertSee($plaintextCodes[9]);

    // The next render no longer has them, and the secret is never re-shown.
    $second = $this->get(route('2fa.manage'))->assertOk();
    $second->assertDontSee($plaintextCodes[0]);
    $second->assertDontSee($record->secret);
});

// ────────────────────────────────────────────────────────────────────────────
// Sign-in challenge
// ────────────────────────────────────────────────────────────────────────────

it('requires a TOTP challenge for an enabled General Manager', function () {
    twoFactorTables();
    $gm = twoFactorUser('general-manager');
    $material = enableTwoFactorFor($gm);

    $this->post('/login-handle', ['login' => $gm->email, 'password' => 'Passw0rd!'])
        ->assertRedirect(route('2fa.challenge'));

    expect($this->app['session']->get('2fa.pending_user_id'))->toBe($gm->id)
        ->and($this->app['session']->get('2fa.verified'))->not->toBeTrue()
        ->and(auth()->id())->toBe($gm->id);

    expect(DB::table('audit_logs')
        ->where('action', '2FA Challenge Initiated')
        ->where('target_id', $gm->id)
        ->exists())->toBeTrue();

    $this->get(route('2fa.challenge'))->assertOk()->assertSee($gm->email);
});

it('rejects a wrong TOTP code during the challenge', function () {
    twoFactorTables();
    $gm = twoFactorUser('general-manager');
    $material = enableTwoFactorFor($gm);

    $this->post('/login-handle', ['login' => $gm->email, 'password' => 'Passw0rd!']);
    $this->post(route('2fa.challenge.attempt'), ['code' => '000000'])
        ->assertSessionHasErrors('code');

    expect($this->app['session']->get('2fa.verified'))->not->toBeTrue();

    expect(DB::table('audit_logs')
        ->where('action', '2FA Challenge Failed')
        ->where('target_id', $gm->id)
        ->exists())->toBeTrue();
});

it('accepts a correct TOTP code, sets 2fa.verified and regenerates the session', function () {
    twoFactorTables();
    $gm = twoFactorUser('general-manager');
    $material = enableTwoFactorFor($gm);

    $this->post('/login-handle', ['login' => $gm->email, 'password' => 'Passw0rd!']);
    $before = $this->app['session']->getId();

    $this->post(route('2fa.challenge.attempt'), ['code' => twoFactorCode($material['secret'])])
        ->assertRedirect(route('UserHandle'))
        ->assertSessionHas('just_logged_in');

    expect($this->app['session']->getId())->not->toBe($before)
        ->and($this->app['session']->get('2fa.verified'))->toBeTrue()
        ->and($this->app['session']->get('2fa.pending_user_id'))->toBeNull()
        ->and(auth()->id())->toBe($gm->id);

    expect(DB::table('audit_logs')
        ->where('action', '2FA TOTP Challenge Succeeded')
        ->where('target_id', $gm->id)
        ->exists())->toBeTrue();

    // The verified session can now reach privileged routes (no challenge bounce).
    $this->get(route('UserHandle'))->assertRedirect(route('dashboard'));
});

it('blocks pending-2FA sessions from privileged pages', function () {
    twoFactorTables();
    $gm = twoFactorUser('general-manager');
    $material = enableTwoFactorFor($gm);

    $this->post('/login-handle', ['login' => $gm->email, 'password' => 'Passw0rd!']);

    $this->get(route('dashboard'))->assertRedirect(route('2fa.challenge'));
    $this->get(route('settings'))->assertRedirect(route('2fa.challenge'));
});

// ────────────────────────────────────────────────────────────────────────────
// Recovery codes
// ────────────────────────────────────────────────────────────────────────────

it('accepts a valid recovery code during the challenge', function () {
    twoFactorTables();
    $gm = twoFactorUser('general-manager');
    $material = enableTwoFactorFor($gm);

    $this->post('/login-handle', ['login' => $gm->email, 'password' => 'Passw0rd!']);

    $this->post(route('2fa.challenge.attempt'), ['code' => $material['codes'][0]])
        ->assertRedirect(route('UserHandle'));

    expect($this->app['session']->get('2fa.verified'))->toBeTrue();

    expect(DB::table('audit_logs')
        ->where('action', '2FA Recovery Code Used')
        ->where('target_id', $gm->id)
        ->exists())->toBeTrue();

    // The used recovery code was consumed.
    $record = two_factor_secret_tbl::where('user_id', $gm->id)->first();
    $hashUsed = Hash::make($material['codes'][0]);
    $remaining = $record->recovery_codes;
    expect(count($remaining))->toBe(9);
    foreach ($remaining as $hash) {
        expect(Hash::check($material['codes'][0], $hash))->toBeFalse();
    }
});

it('rejects a reused recovery code', function () {
    twoFactorTables();
    $gm = twoFactorUser('general-manager');
    $material = enableTwoFactorFor($gm);

    $this->post('/login-handle', ['login' => $gm->email, 'password' => 'Passw0rd!']);
    $this->post(route('2fa.challenge.attempt'), ['code' => $material['codes'][0]])->assertRedirect(route('UserHandle'));
    $this->get(route('logout'));

    $this->post('/login-handle', ['login' => $gm->email, 'password' => 'Passw0rd!'])
        ->assertRedirect(route('2fa.challenge'));

    $this->post(route('2fa.challenge.attempt'), ['code' => $material['codes'][0]])
        ->assertSessionHasErrors('code');

    expect($this->app['session']->get('2fa.verified'))->not->toBeTrue();
});

it('rejects an invalid recovery code', function () {
    twoFactorTables();
    $gm = twoFactorUser('general-manager');
    $material = enableTwoFactorFor($gm);

    $this->post('/login-handle', ['login' => $gm->email, 'password' => 'Passw0rd!']);

    $this->post(route('2fa.challenge.attempt'), ['code' => 'ABCD-EFGH-IJKL'])
        ->assertSessionHasErrors('code');

    expect($this->app['session']->get('2fa.verified'))->not->toBeTrue();
});

it('regenerating recovery codes invalidates the previous set', function () {
    twoFactorTables();
    $gm = twoFactorUser('general-manager');
    $material = enableTwoFactorFor($gm);

    signIn($gm);

    $this->post(route('2fa.recovery.regenerate'), ['code' => '123456'])
        ->assertSessionHasErrors('code');

    $this->post(route('2fa.recovery.regenerate'), ['code' => $material['codes'][0]])
        ->assertRedirect(route('2fa.manage'));

    expect($this->app['session']->get('2fa.new-recovery-codes'))
        ->toBeArray()
        ->and(count($this->app['session']->get('2fa.new-recovery-codes')))->toBe(10);

    $oldCodesEncoded = two_factor_secret_tbl::where('user_id', $gm->id)->value('recovery_codes');
    $record = two_factor_secret_tbl::where('user_id', $gm->id)->first();
    expect($record->recovery_codes)->toBeArray()
        ->and(count($record->recovery_codes))->toBe(10);
    foreach ($material['codes'] as $old) {
        foreach ($record->recovery_codes as $hash) {
            expect(Hash::check($old, $hash))->toBeFalse();
        }
    }
    expect(DB::table('two_factor_secrets_tbls')->where('user_id', $gm->id)->value('recovery_codes'))
        ->not->toBe($oldCodesEncoded);

    expect(DB::table('audit_logs')
        ->where('action', '2FA Recovery Codes Regenerated')
        ->where('target_id', $gm->id)
        ->exists())->toBeTrue();

    // Old codes no longer unlock the account (and the used one was consumed).
    $this->get(route('logout'));
    $this->post('/login-handle', ['login' => $gm->email, 'password' => 'Passw0rd!'])
        ->assertRedirect(route('2fa.challenge'));
    $this->post(route('2fa.challenge.attempt'), ['code' => $material['codes'][1]])
        ->assertSessionHasErrors('code');
});

// ────────────────────────────────────────────────────────────────────────────
// Disable
// ────────────────────────────────────────────────────────────────────────────

it('rejects disabling 2FA without a valid code', function () {
    twoFactorTables();
    $gm = twoFactorUser('general-manager');
    $material = enableTwoFactorFor($gm);
    signIn($gm);

    $this->post(route('2fa.disable'), ['code' => '123456'])
        ->assertSessionHasErrors('code');

    expect(two_factor_secret_tbl::where('user_id', $gm->id)->exists())->toBeTrue();
});

it('rejects disabling 2FA with an invalid code', function () {
    twoFactorTables();
    $gm = twoFactorUser('general-manager');
    $material = enableTwoFactorFor($gm);
    signIn($gm);

    $this->post(route('2fa.disable'), ['code' => '999999'])
        ->assertSessionHasErrors('code');

    $record = two_factor_secret_tbl::where('user_id', $gm->id)->first();
    expect($record)->not->toBeNull()
        ->and((bool) $record->enabled)->toBeTrue()
        ->and($record->secret)->not->toBeNull();
});

it('disables 2FA when a valid TOTP code is provided and removes the configuration', function () {
    twoFactorTables();
    $gm = twoFactorUser('general-manager');
    $material = enableTwoFactorFor($gm);
    signIn($gm);

    $this->post(route('2fa.disable'), ['code' => twoFactorCode($material['secret'])])
        ->assertRedirect(route('2fa.manage'))
        ->assertSessionHas('status');

    expect(two_factor_secret_tbl::where('user_id', $gm->id)->exists())->toBeFalse();
    expect($this->app['session']->get('2fa.verified'))->not->toBeTrue();

    expect(DB::table('audit_logs')
        ->where('action', '2FA Disabled')
        ->where('target_id', $gm->id)
        ->exists())->toBeTrue();
});

it('disables 2FA when a valid recovery code is provided', function () {
    twoFactorTables();
    $gm = twoFactorUser('general-manager');
    $material = enableTwoFactorFor($gm);
    signIn($gm);

    $this->post(route('2fa.disable'), ['code' => $material['codes'][1]])
        ->assertRedirect(route('2fa.manage'));

    expect(two_factor_secret_tbl::where('user_id', $gm->id)->exists())->toBeFalse();
});

// ────────────────────────────────────────────────────────────────────────────
// Eligibility
// ────────────────────────────────────────────────────────────────────────────

it('protects the Main Admin when 2FA is enabled', function () {
    twoFactorTables();
    $mainAdmin = twoFactorUser('admin');
    enableTwoFactorFor($mainAdmin);

    $this->post('/login-handle', ['login' => $mainAdmin->email, 'password' => 'Passw0rd!'])
        ->assertRedirect(route('2fa.challenge'));
});

it('does not force 2FA on an ordinary member', function () {
    twoFactorTables();
    $member = twoFactorUser('member', 'member');

    $this->post('/login-handle', ['login' => $member->email, 'password' => 'Passw0rd!'])
        ->assertRedirect(route('UserHandle'));

    expect($this->app['session']->get('2fa.pending_user_id'))->toBeNull();
});

it('does not force 2FA on other staff or non-first admins', function () {
    twoFactorTables();
    twoFactorUser('admin', null);            // first admin = Main Admin
    $otherAdmin = twoFactorUser('admin', null);
    $cashier = twoFactorUser('cashier', null);
    $aw = twoFactorUser('cashier', 'member'); // Allied Worker (member-based staff)

    // Non-Main-Admin admins and unrelated staff are not routed through 2FA.
    foreach ([$otherAdmin, $cashier] as $u) {
        $this->post('/login-handle', ['login' => $u->email, 'password' => 'Passw0rd!'])
            ->assertRedirect(route('UserHandle'));
        expect($this->app['session']->get('2fa.pending_user_id'))->toBeNull();
        $this->get(route('logout'));
    }

    expect($otherAdmin->requiresTwoFactor())->toBeFalse()
        ->and($cashier->requiresTwoFactor())->toBeFalse()
        ->and($aw->requiresTwoFactor())->toBeFalse();
});

it('prevents non-eligible accounts from enrolling or managing 2FA', function () {
    twoFactorTables();
    $member = twoFactorUser('member', 'member');
    twoFactorUser('admin', null);
    $otherAdmin = twoFactorUser('admin', null);

    foreach ([$member, $otherAdmin] as $u) {
        signIn($u);

        $this->get(route('2fa.manage'))->assertStatus(403);
        $this->post(route('2fa.enroll'))->assertStatus(403);
        $this->post(route('2fa.confirm'), ['code' => '123456'])->assertStatus(403);
        $this->post(route('2fa.disable'), ['code' => '123456'])->assertStatus(403);
        $this->post(route('2fa.recovery.regenerate'), ['code' => '123456'])->assertStatus(403);

        $this->app['session']->forget('2fa.verified');
        auth()->logout();
    }
});

// ────────────────────────────────────────────────────────────────────────────
// Throttling
// ────────────────────────────────────────────────────────────────────────────

it('rate-limits the 2FA challenge to 5 attempts per minute per user/IP', function () {
    twoFactorTables();
    $gm = twoFactorUser('general-manager');
    $material = enableTwoFactorFor($gm);

    $this->call('POST', '/login-handle', ['login' => $gm->email, 'password' => 'Passw0rd!'], [], [], ['REMOTE_ADDR' => '203.0.113.9']);

    for ($i = 0; $i < 5; $i++) {
        $this->call('POST', route('2fa.challenge.attempt'), ['code' => '111111'], [], [], ['REMOTE_ADDR' => '203.0.113.9']);
    }

    $this->call('POST', route('2fa.challenge.attempt'), ['code' => '222222'], [], [], ['REMOTE_ADDR' => '203.0.113.9'])
        ->assertStatus(429);
});

// ────────────────────────────────────────────────────────────────────────────
// Security
// ────────────────────────────────────────────────────────────────────────────

it('never writes secrets, codes or credentials into audit details', function () {
    twoFactorTables();
    $gm = twoFactorUser('general-manager');
    $material = enableTwoFactorFor($gm);

    // Natural sign-in, a failed challenge, then a successful one.
    $this->post('/login-handle', ['login' => $gm->email, 'password' => 'Passw0rd!']);
    $this->post(route('2fa.challenge.attempt'), ['code' => '000000']);
    $this->post(route('2fa.challenge.attempt'), ['code' => twoFactorCode($material['secret'])]);

    $rows = DB::table('audit_logs')->pluck('details');
    foreach ($rows as $details) {
        expect((string) $details)->not->toContain($material['secret']);
        foreach ($material['codes'] as $code) {
            expect((string) $details)->not->toContain($code);
        }
        expect((string) $details)->not->toContain('Passw0rd!');
    }
});

it('password reset does not weaken or disable 2FA', function () {
    twoFactorTables();
    $gm = twoFactorUser('general-manager');
    $material = enableTwoFactorFor($gm);

    $token = Password::broker()->createToken($gm);

    $this->post(route('password.update'), [
        'token' => $token,
        'email' => $gm->email,
        'password' => 'NewPass123!',
        'password_confirmation' => 'NewPass123!',
    ])->assertRedirect(route('login'));

    $record = two_factor_secret_tbl::where('user_id', $gm->id)->first();
    expect($record)->not->toBeNull()
        ->and((bool) $record->enabled)->toBeTrue()
        ->and($record->secret)->toBe($material['secret'])
        ->and(count($record->recovery_codes))->toBe(10);

    expect(DB::table('audit_logs')->where('action', '2FA Disabled')->where('target_id', $gm->id)->exists())->toBeFalse();

    // Signing back in with the new password still requires 2FA.
    $this->post('/login-handle', ['login' => $gm->email, 'password' => 'NewPass123!'])
        ->assertRedirect(route('2fa.challenge'));
});