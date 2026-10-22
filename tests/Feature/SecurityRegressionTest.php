<?php

use App\Models\PaymentMethod;
use App\Models\Users_tbl;
use App\Services\PatronageSources\LoanRepaymentPatronageSource;
use App\Services\SoDGuard;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

// NOTE: phpunit.xml runs against sqlite :memory: and RefreshDatabase is
// disabled in tests/Pest.php, so every table a test touches must be created
// by hand here (drop-if-exists keeps each test isolated).

function securityAccessTables(): void
{
    Schema::dropIfExists('users_tbls');

    Schema::create('users_tbls', function (Blueprint $table) {
        $table->id();
        $table->string('first_name')->nullable();
        $table->string('last_name')->nullable();
        $table->string('email')->nullable();
        $table->string('password')->nullable();
        $table->string('role')->default('member');
        $table->string('base_role')->nullable();
        $table->string('status')->nullable();
        $table->timestamps();
    });

    Schema::dropIfExists('payment_methods_tbls');
    Schema::create('payment_methods_tbls', function (Blueprint $table) {
        $table->id();
        $table->string('method_name');
        $table->boolean('has_qr_code')->default(false);
        $table->string('qr_code_image_path')->nullable();
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });

    PaymentMethod::create(['method_name' => 'Cash', 'is_active' => true]);
}

function plainMember(): Users_tbl
{
    return Users_tbl::create([
        'first_name' => 'Plain',
        'last_name' => 'Member',
        'email' => 'plain@member.local',
        'password' => bcrypt('secret'),
        'role' => 'member',
        'status' => 'Active',
    ]);
}

function staffAccount(): Users_tbl
{
    return Users_tbl::create([
        'first_name' => 'Staff',
        'last_name' => 'Admin',
        'email' => 'staff@admin.local',
        'password' => bcrypt('secret'),
        'role' => 'admin',
        'status' => 'Active',
    ]);
}

// ────────────────────────────────────────────────────────────────────────────
// PayMongo removal + route hardening
// ────────────────────────────────────────────────────────────────────────────

it('removes the PayMongo payment routes entirely', function () {
    expect(Route::has('share_capital.gcash'))->toBeFalse();
    expect(Route::has('savings.gcash'))->toBeFalse();
    expect(Route::has('repayment.gcash'))->toBeFalse();
    expect(Route::has('gcash-share'))->toBeFalse();
});

it('removes the /debug-db route entirely', function () {
    $this->get('/debug-db')->assertStatus(404);
});

it('keeps PaymentMethodController routes registered (formerly debug-only)', function () {
    expect(Route::has('payment-methods.index'))->toBeTrue();
});

// ────────────────────────────────────────────────────────────────────────────
// Staff-gate ('admin') middleware access control
// ────────────────────────────────────────────────────────────────────────────

it('redirects guests away from admin routes', function () {
    securityAccessTables();

    $this->get('/admin/payment-methods')->assertRedirect(route('login'));
});

it('blocks plain members from admin routes with 403', function () {
    securityAccessTables();

    $this->actingAs(plainMember())
        ->get('/admin/payment-methods')
        ->assertStatus(403);
});

it('allows staff accounts through the admin gate', function () {
    securityAccessTables();

    $this->actingAs(staffAccount())
        ->get('/admin/payment-methods')
        ->assertOk()
        ->assertJson(['success' => true]);
});

it('approve-user is POST-only and staff-gated', function () {
    securityAccessTables();

    // GET no longer exposes the state change.
    $this->get(route('approve.user', 1))->assertStatus(405);

    // Plain members are blocked by the staff gate.
    $this->actingAs(plainMember())
        ->post(route('approve.user', 1))
        ->assertStatus(403);
});

it('guests are redirected to login before the admin gate', function () {
    securityAccessTables();

    $this->post(route('approve.user', 1))->assertRedirect(route('login'));

    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

it('member-facing announcement actions still work for authenticated members', function () {
    securityAccessTables();

    expect(Route::has('announcements.comment'))->toBeTrue();
    expect(Route::has('announcements.like'))->toBeTrue();
    expect(Route::has('announcements.poll.vote'))->toBeTrue();
});

it('SoDGuard actingAsStaff matches the staff-gate semantics', function () {
    securityAccessTables();

    expect(SoDGuard::actingAsStaff(plainMember()))->toBeFalse();
    expect(SoDGuard::actingAsStaff(staffAccount()))->toBeTrue();
});

// ────────────────────────────────────────────────────────────────────────────
// Rate limiting
// ────────────────────────────────────────────────────────────────────────────

it('rate-limits login attempts per account and IP', function () {
    for ($i = 1; $i <= 5; $i++) {
        $this->post('/login-handle', ['login' => 'attacker@example.com', 'password' => 'wrong']);
    }

    $this->post('/login-handle', ['login' => 'attacker@example.com', 'password' => 'wrong'])
        ->assertStatus(429);
});

it('rate-limits OTP sending attempts by IP', function () {
    for ($i = 1; $i <= 5; $i++) {
        $this->post('/otp/send', ['email' => 'otp@example.com']);
    }

    $this->post('/otp/send', ['email' => 'otp@example.com'])->assertStatus(429);
});

it('rate-limits email-check lookups by IP', function () {
    securityAccessTables();

    for ($i = 1; $i <= 30; $i++) {
        $this->post('/check-email', ['email' => 'lookup@example.com']);
    }

    $this->post('/check-email', ['email' => 'lookup@example.com'])->assertStatus(429);
});

// ────────────────────────────────────────────────────────────────────────────
// Global security headers
// ────────────────────────────────────────────────────────────────────────────

it('sends global security headers on every response', function () {
    $response = $this->get(route('login'));

    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    $response->assertHeader('Content-Security-Policy');
    // No HSTS over plain HTTP.
    $response->assertHeaderMissing('Strict-Transport-Security');
});

// ────────────────────────────────────────────────────────────────────────────
// Model hardening
// ────────────────────────────────────────────────────────────────────────────

it('never serializes password hashes from user models', function () {
    securityAccessTables();

    $staff = staffAccount();

    expect(array_key_exists('password', $staff->getAttributes()))->toBeTrue();
    expect(array_key_exists('password', $staff->toArray()))->toBeFalse();
    expect(str_contains($staff->toJson(), '"password"'))->toBeFalse();
});

// ────────────────────────────────────────────────────────────────────────────
// Date-filter validation
// ────────────────────────────────────────────────────────────────────────────

it('rejects non-date values for whereBetween date filters', function () {
    $probe = new DateValidationProbe();

    expect($probe->probe(Request::create('/?from_date=<script>alert(1)</script>', 'GET'), 'from_date', '2026-01-01'))
        ->toBe('2026-01-01');
    expect($probe->probe(Request::create('/?from_date=2026-13-40', 'GET'), 'from_date', '2026-01-01'))
        ->toBe('2026-01-01');
    expect($probe->probe(Request::create('/?from_date=2026-05-07', 'GET'), 'from_date', '2026-01-01'))
        ->toBe('2026-05-07');
});

// ────────────────────────────────────────────────────────────────────────────
// Patronage income-expression guard
// ────────────────────────────────────────────────────────────────────────────

function patronageTables(): void
{
    Schema::dropIfExists('lending_repayments_tbls');
    Schema::create('lending_repayments_tbls', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id')->nullable();
        $table->date('payment_date')->nullable();
        $table->decimal('amount_paid', 12, 2)->default(0);
        $table->decimal('interest_paid', 12, 2)->nullable();
        $table->decimal('service_fee_paid', 12, 2)->nullable();
        $table->decimal('late_fee', 12, 2)->nullable();
        $table->timestamps();
    });

    Schema::dropIfExists('dividend_distributions');
    Schema::create('dividend_distributions', function (Blueprint $table) {
        $table->id();
        $table->integer('year')->unique();
        $table->string('patronage_basis', 50)->nullable();
        $table->timestamps();
    });
}

it('falls back safely when a tampered patronage basis is stored', function () {
    patronageTables();

    // Legacy repayment without an income breakdown → full amount counts.
    DB::table('lending_repayments_tbls')->insert([
        'user_id' => 1,
        'payment_date' => '2026-03-01',
        'amount_paid' => 500.00,
        'interest_paid' => null,
        'service_fee_paid' => null,
        'late_fee' => null,
    ]);

    // Modern repayment with a full income breakdown → income components only.
    DB::table('lending_repayments_tbls')->insert([
        'user_id' => 1,
        'payment_date' => '2026-04-01',
        'amount_paid' => 1000.00,
        'interest_paid' => 100.00,
        'service_fee_paid' => 50.00,
        'late_fee' => 10.00,
    ]);

    // Attacker-tampered snapshot value — must not reach the SQL expression.
    DB::table('dividend_distributions')->insert([
        'year' => 2026,
        'patronage_basis' => "total_repayment' OR '1'='1",
    ]);

    $source = new LoanRepaymentPatronageSource();
    $result = $source->getAllPatronageForYear(2026);

    expect($result)->toHaveCount(1);
    // total_repayment: 500 (legacy) + 160 (100 interest + 50 fee + 10 late) = 660
    expect((float) $result[1])->toBe(660.0);
});

it('honors a legitimate net_repayment basis without counting late fees', function () {
    patronageTables();

    DB::table('lending_repayments_tbls')->insert([
        'user_id' => 3,
        'payment_date' => '2026-05-01',
        'amount_paid' => 1000.00,
        'interest_paid' => 100.00,
        'service_fee_paid' => 50.00,
        'late_fee' => 10.00,
    ]);

    DB::table('dividend_distributions')->insert([
        'year' => 2026,
        'patronage_basis' => 'net_repayment',
    ]);

    $source = new LoanRepaymentPatronageSource();

    expect((float) $source->getAllPatronageForYear(2026)[3])->toBe(150.0);
});

class DateValidationProbe extends App\Http\Controllers\Controller
{
    public function probe(Request $request, string $key, string $default): string
    {
        return $this->validDateParam($request, $key, $default);
    }
}