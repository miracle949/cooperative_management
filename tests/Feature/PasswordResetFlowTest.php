<?php

use App\Models\Users_tbl;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Schema;

// NOTE: phpunit.xml runs against sqlite :memory: and RefreshDatabase is
// disabled in tests/Pest.php, so every table a test touches must be created
// by hand here (drop-if-exists keeps each test isolated).

const PASSWORD_RESET_GENERIC_STATUS = 'If an account exists for that email, a password reset link is on its way.';

function passwordResetTables(): void
{
    Schema::dropIfExists('password_reset_tokens');
    Schema::create('password_reset_tokens', function (Blueprint $table) {
        $table->string('email')->primary();
        $table->string('token');
        $table->timestamp('created_at')->nullable();
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
}

function resetUser(string $email = 'reset.user@gmail.com', string $password = 'OldPass1!'): Users_tbl
{
    $user = Users_tbl::create([
        'first_name' => 'Reset',
        'last_name' => 'User',
        'username' => 'resetuser',
        'email' => $email,
        'password' => bcrypt($password),
        'role' => 'member',
        'base_role' => 'member',
        'status' => 'Active',
    ]);

    DB::table('otherinfo_tbls')->insertOrIgnore([
        'user_id' => $user->id,
        'approval_status' => 'Approved',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return $user;
}

// ────────────────────────────────────────────────────────────────────────────
// Forgot-password request + reset email
// ────────────────────────────────────────────────────────────────────────────

it('sends a reset email and stores the token hashed at rest', function () {
    passwordResetTables();
    Notification::fake();
    $user = resetUser();

    $this->post(route('password.email'), ['email' => $user->email])
        ->assertSessionHas('status', PASSWORD_RESET_GENERIC_STATUS);

    $token = null;
    Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use (&$token) {
        $token = $notification->token;

        return true;
    });

    expect($token)->not->toBeNull();

    $stored = DB::table('password_reset_tokens')->where('email', $user->email)->first();
    expect($stored)->not->toBeNull()
        ->and($stored->token)->not->toBe($token);

    // The framework email template renders a working reset link.
    $mail = (new ResetPassword($token))->toMail($user);
    expect((string) $mail->render())->toContain('/reset-password/');
});

it('serves the forgot-password and reset forms', function () {
    $this->get(route('password.request'))->assertOk();
    $this->get(route('password.reset', 'sometoken'))->assertOk();
});

// ────────────────────────────────────────────────────────────────────────────
// Non-enumerating response
// ────────────────────────────────────────────────────────────────────────────

it('returns the same generic response whether or not the email exists', function () {
    passwordResetTables();

    $this->post(route('password.email'), ['email' => 'reset.user@gmail.com'])
        ->assertSessionHas('status', PASSWORD_RESET_GENERIC_STATUS);

    $this->post(route('password.email'), ['email' => 'nobody@nowhere.com'])
        ->assertSessionHas('status', PASSWORD_RESET_GENERIC_STATUS);

    expect(DB::table('password_reset_tokens')->where('email', 'nobody@nowhere.com')->exists())->toBeFalse();
});

// ────────────────────────────────────────────────────────────────────────────
// Valid reset
// ────────────────────────────────────────────────────────────────────────────

it('resets the password with a valid token and sets password_changed_at', function () {
    passwordResetTables();
    $user = resetUser();
    $token = Password::broker()->createToken($user);

    $this->post(route('password.update'), [
        'token' => $token,
        'email' => $user->email,
        'password' => 'NewStrong1!',
        'password_confirmation' => 'NewStrong1!',
    ])->assertRedirect(route('login'));

    $fresh = $user->fresh();
    expect(Hash::check('NewStrong1!', $fresh->password))->toBeTrue()
        ->and(Hash::check('OldPass1!', $fresh->password))->toBeFalse()
        ->and($fresh->password_changed_at)->not->toBeNull()
        ->and(DB::table('password_reset_tokens')->where('email', $user->email)->exists())->toBeFalse();

    expect(DB::table('audit_logs')
        ->where('action', 'Password Reset')
        ->where('admin_name', 'System')
        ->where('target_type', 'user')
        ->where('target_id', $user->id)
        ->exists())->toBeTrue();
});

// ────────────────────────────────────────────────────────────────────────────
// Invalid / expired / reused tokens
// ────────────────────────────────────────────────────────────────────────────

it('rejects an invalid token', function () {
    passwordResetTables();
    $user = resetUser();

    $this->post(route('password.update'), [
        'token' => 'not-a-real-token',
        'email' => $user->email,
        'password' => 'NewStrong1!',
        'password_confirmation' => 'NewStrong1!',
    ])->assertSessionHasErrors('email');

    expect(Hash::check('OldPass1!', $user->fresh()->password))->toBeTrue();
});

it('rejects an expired token', function () {
    passwordResetTables();
    $user = resetUser();
    $token = Password::broker()->createToken($user);

    DB::table('password_reset_tokens')
        ->where('email', $user->email)
        ->update(['created_at' => now()->subMinutes(61)]);

    $this->post(route('password.update'), [
        'token' => $token,
        'email' => $user->email,
        'password' => 'NewStrong1!',
        'password_confirmation' => 'NewStrong1!',
    ])->assertSessionHasErrors('email');

    expect(Hash::check('OldPass1!', $user->fresh()->password))->toBeTrue();
});

it('rejects a reused token after a successful reset', function () {
    passwordResetTables();
    $user = resetUser();
    $token = Password::broker()->createToken($user);

    $this->post(route('password.update'), [
        'token' => $token,
        'email' => $user->email,
        'password' => 'NewStrong1!',
        'password_confirmation' => 'NewStrong1!',
    ])->assertRedirect(route('login'));

    $this->post(route('password.update'), [
        'token' => $token,
        'email' => $user->email,
        'password' => 'Another1!',
        'password_confirmation' => 'Another1!',
    ])->assertSessionHasErrors('email');

    expect(Hash::check('NewStrong1!', $user->fresh()->password))->toBeTrue();
});

// ────────────────────────────────────────────────────────────────────────────
// Password requirements on reset
// ────────────────────────────────────────────────────────────────────────────

it('rejects a weak password on reset', function () {
    passwordResetTables();
    $user = resetUser();
    $token = Password::broker()->createToken($user);

    $this->post(route('password.update'), [
        'token' => $token,
        'email' => $user->email,
        'password' => 'short7c',
        'password_confirmation' => 'short7c',
    ])->assertSessionHasErrors('password');

    expect(Hash::check('OldPass1!', $user->fresh()->password))->toBeTrue();
});

it('rejects a mismatched password confirmation on reset', function () {
    passwordResetTables();
    $user = resetUser();
    $token = Password::broker()->createToken($user);

    $this->post(route('password.update'), [
        'token' => $token,
        'email' => $user->email,
        'password' => 'NewStrong1!',
        'password_confirmation' => 'Different1!',
    ])->assertSessionHasErrors('password');

    expect(Hash::check('OldPass1!', $user->fresh()->password))->toBeTrue();
});

// ────────────────────────────────────────────────────────────────────────────
// Login after reset
// ────────────────────────────────────────────────────────────────────────────

it('allows login with the new password after a reset', function () {
    passwordResetTables();
    $user = resetUser();
    $token = Password::broker()->createToken($user);

    $this->post(route('password.update'), [
        'token' => $token,
        'email' => $user->email,
        'password' => 'NewStrong1!',
        'password_confirmation' => 'NewStrong1!',
    ])->assertRedirect(route('login'));

    // Old password no longer authenticates.
    $this->post('/login-handle', ['login' => $user->email, 'password' => 'OldPass1!'])
        ->assertSessionHasErrors('login');

    // New password logs the member in and continues the normal member flow.
    $this->post('/login-handle', ['login' => $user->email, 'password' => 'NewStrong1!'])
        ->assertRedirect(route('UserHandle'));
});

// ────────────────────────────────────────────────────────────────────────────
// Throttling
// ────────────────────────────────────────────────────────────────────────────

it('rate-limits password reset requests by IP', function () {
    passwordResetTables();

    for ($i = 0; $i < 5; $i++) {
        $this->call('POST', route('password.email'), ['email' => 'throttle@example.com'], [], [], ['REMOTE_ADDR' => '203.0.113.77']);
    }

    $this->call('POST', route('password.email'), ['email' => 'throttle@example.com'], [], [], ['REMOTE_ADDR' => '203.0.113.77'])
        ->assertStatus(429);
});