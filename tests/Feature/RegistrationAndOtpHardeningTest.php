<?php

use App\Models\Users_tbl;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

// NOTE: phpunit.xml runs against sqlite :memory: and RefreshDatabase is
// disabled in tests/Pest.php, so every table a test touches must be created
// by hand here (drop-if-exists keeps each test isolated).
//
// Known blocker for route-level rejection tests: the registration controller
// wraps its validate() in `try { ... } catch (\Exception $e) { dd(...) }`
// (UsersHandle.php:3043), so ANY server-side validation failure dd()s and
// exits the process. That is a pre-existing defect (out of U1 scope), so weak/
// mismatch rejections are covered at the Validator level + a source assertion
// on the exact rule string, while acceptance is covered end-to-end.

function registrationTables(): void
{
    Schema::dropIfExists('users_tbls');
    Schema::create('users_tbls', function (Blueprint $table) {
        $table->id();
        $table->string('first_name')->nullable();
        $table->string('middle_name')->nullable();
        $table->string('last_name')->nullable();
        $table->string('username')->nullable();
        $table->string('email')->nullable();
        $table->string('password')->nullable();
        $table->string('role')->nullable();
        $table->timestamps();
    });

    Schema::dropIfExists('family_tbls');
    Schema::create('family_tbls', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id')->nullable();
        $table->string('spouse_name')->nullable();
        $table->date('spouse_date_birth')->nullable();
        $table->string('spouse_place_birth')->nullable();
        $table->integer('number_son')->nullable();
        $table->integer('number_daughter')->nullable();
        $table->string('other_spec')->nullable();
        $table->timestamps();
    });

    Schema::dropIfExists('membergovern_ids_tbls');
    Schema::create('membergovern_ids_tbls', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id')->nullable();
        $table->string('sss_id')->nullable();
        $table->string('philhealth_id')->nullable();
        $table->string('pagibig_id')->nullable();
        $table->string('tin_id')->nullable();
        $table->timestamps();
    });

    Schema::dropIfExists('otherinfo_tbls');
    Schema::create('otherinfo_tbls', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id')->nullable();
        $table->string('membership_category')->nullable();
        $table->boolean('email_verified')->nullable();
        $table->date('date_of_birth')->nullable();
        $table->string('place_of_birth')->nullable();
        $table->string('sex')->nullable();
        $table->string('civil_status')->nullable();
        $table->string('citizenship')->nullable();
        $table->string('skills')->nullable();
        $table->text('signature')->nullable();
        $table->string('profile_picture')->nullable();
        $table->string('approval_status')->nullable();
        $table->string('membership_status')->nullable();
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
}

function registrationPasswordRules(): array
{
    return ['password' => 'required|string|min:8|confirmed'];
}

function validRegistrationPayload(array $overrides = []): array
{
    return array_merge([
        'first_name' => 'Test',
        'last_name' => 'Member',
        'username' => 'testmember',
        'email' => 'test.member@gmail.com',
        'password' => 'StrongPass1!',
        'password_confirmation' => 'StrongPass1!',
        'date_of_birth' => '1990-01-01',
        'place_of_birth' => 'Manila',
        'membership_category' => 'Operator',
        'civil_status' => 'Single',
        'sex' => 'Male',
        'citizenship' => 'Filipino',
        'signature' => 'data:image/png;base64,AAAA',
    ], $overrides);
}

// ────────────────────────────────────────────────────────────────────────────
// Registration password validation (required|string|min:8|confirmed)
// ────────────────────────────────────────────────────────────────────────────

it('applies the min:8 confirmed password rule to registration', function () {
    $controllerSource = file_get_contents(base_path('app/Http/Controllers/UsersHandle.php'));
    expect($controllerSource)->toContain("'password' => 'required|string|min:8|confirmed',")
        ->and($controllerSource)->not->toContain("'password' => 'required|confirmed',");
});

it('rejects passwords shorter than 8 characters under the registration rule', function () {
    $data = validRegistrationPayload(['password' => 'short7c', 'password_confirmation' => 'short7c']);
    $validator = Validator::make($data, registrationPasswordRules());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('password'))->toContain('must be at least 8 characters');
});

it('rejects mismatched password confirmations under the registration rule', function () {
    $data = validRegistrationPayload(['password_confirmation' => 'DifferentPass1!']);
    $validator = Validator::make($data, registrationPasswordRules());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('password'))->toContain('does not match');
});

it('accepts a valid registration with a strong password', function () {
    registrationTables();

    $this->post('/registration', validRegistrationPayload())
        ->assertRedirect(route('RegisterPage'));

    $user = Users_tbl::where('email', 'test.member@gmail.com')->first();

    expect($user)->not->toBeNull()
        ->and(Hash::check('StrongPass1!', $user->password))->toBeTrue()
        ->and($user->role)->toBe('Pending');
});

// ────────────────────────────────────────────────────────────────────────────
// OTP generation must use cryptographically secure randomness
// ────────────────────────────────────────────────────────────────────────────

it('uses cryptographically secure randomness in the OTP generation path', function () {
    $source = file_get_contents(base_path('app/Http/Controllers/OtpController.php'));

    expect($source)->toContain('random_int(100000, 999999)')
        ->and($source)->not->toContain('rand(')
        ->and($source)->not->toContain('mt_rand(');
});

// ────────────────────────────────────────────────────────────────────────────
// Registration rate limiting (IP-keyed; registration is unauthenticated)
// ────────────────────────────────────────────────────────────────────────────

it('rate-limits registration attempts by IP', function () {
    registrationTables();

    for ($i = 0; $i < 10; $i++) {
        $this->call('POST', '/registration', validRegistrationPayload([
            'email' => "burst.member{$i}@gmail.com",
            'username' => "burstmember{$i}",
        ]), [], [], ['REMOTE_ADDR' => '203.0.113.10']);
    }

    // 11th attempt from the same IP is throttled (before reaching the controller).
    $this->call('POST', '/registration', validRegistrationPayload(), [], [], ['REMOTE_ADDR' => '203.0.113.10'])
        ->assertStatus(429);

    // A different IP keeps its own bucket and is not throttled.
    $response = $this->call('POST', '/registration', validRegistrationPayload([
        'email' => 'other.ip@gmail.com',
        'username' => 'otherip',
    ]), [], [], ['REMOTE_ADDR' => '198.51.100.42']);

    expect($response->status())->not->toBe(429);
});

// ────────────────────────────────────────────────────────────────────────────
// Pending applicants must never be able to log in, even when their otherinfo
// approval_status has diverged from users_tbls.role.
// ────────────────────────────────────────────────────────────────────────────

it('blocks login for a Pending applicant even when otherinfo approval has diverged', function () {
    registrationTables();

    $user = Users_tbl::create([
        'first_name' => 'Zuroa',
        'last_name' => 'Zack',
        'username' => 'zuroa.pending',
        'email' => 'pending.applicant@example.com',
        'password' => Hash::make('Str0ngPass1'),
        'role' => 'Pending',
    ]);

    DB::table('otherinfo_tbls')->insert([
        'user_id' => $user->id,
        'approval_status' => 'Approved',
        'membership_status' => 'Active',
    ]);

    $response = $this->from(route('login'))->post('/login-handle', [
        'login' => 'pending.applicant@example.com',
        'password' => 'Str0ngPass1',
    ]);

    $response->assertRedirect(route('login'));

    expect(auth()->guest())->toBeTrue()
        ->and(session('errors')->first('login'))->toContain('still pending approval');
});

it('allows login once the applicant has been promoted to member', function () {
    registrationTables();

    $user = Users_tbl::create([
        'first_name' => 'Zuroa',
        'last_name' => 'Zack',
        'username' => 'zuroa.promo',
        'email' => 'promoted.member@example.com',
        'password' => Hash::make('Str0ngPass1'),
        'role' => 'Member',
    ]);

    DB::table('otherinfo_tbls')->insert([
        'user_id' => $user->id,
        'approval_status' => 'Approved',
        'membership_status' => 'Active',
    ]);

    $response = $this->from(route('login'))->post('/login-handle', [
        'login' => 'promoted.member@example.com',
        'password' => 'Str0ngPass1',
    ]);

    $response->assertRedirect(route('UserHandle'));

    expect(auth()->user()->email)->toBe('promoted.member@example.com');
});