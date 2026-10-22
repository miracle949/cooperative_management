<?php

use App\Models\Otherinfo_tbl;
use App\Models\Users_tbl;
use App\Models\savings_account_tbl;
use App\Models\savings_transaction_tbl;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

// NOTE: phpunit.xml runs against sqlite :memory: and RefreshDatabase is
// disabled in tests/Pest.php, so every table a test touches must be created
// by hand (drop-if-exists keeps each test isolated).
//
// These tests pin U7 authorization fixes:
//  - ShareCapital::showForMember no longer auto-logs-in as the member id in
//    the public email link URL (full account takeover) — owner/staff only.
//  - The application form GET/POST may only be reached by the account owner,
//    an unauthenticated Pending applicant in self-service, or staff (stops
//    anonymous overwriting of a Member's email -> password-reset takeover).
//  - Savings receipts are scoped to the authenticated owner or staff.
//  - /static-page (renders every user record) is admin-only.
//  - message.user share-capital invitation is POST-only.

function sensitiveRouteTables(): void
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
        $table->string('base_role')->nullable();
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
        $table->string('contact_no')->nullable();
        $table->string('present_address')->nullable();
        $table->string('permanent_address')->nullable();
        $table->string('sex')->nullable();
        $table->string('civil_status')->nullable();
        $table->string('citizenship')->nullable();
        $table->string('height')->nullable();
        $table->string('weight')->nullable();
        $table->string('blood_type')->nullable();
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

    Schema::dropIfExists('membervehi_tbls');
    Schema::create('membervehi_tbls', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id')->nullable();
        $table->string('plate_no')->nullable();
        $table->string('vehicle_type')->nullable();
        $table->integer('quantity')->nullable();
        $table->timestamps();
    });

    Schema::dropIfExists('educational_tbls');
    Schema::create('educational_tbls', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id')->nullable();
        $table->string('educational_level')->nullable();
        $table->string('status')->nullable();
        $table->string('specify')->nullable();
        $table->timestamps();
    });

    Schema::dropIfExists('share_capital_account_tbls');
    Schema::create('share_capital_account_tbls', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id')->nullable();
        $table->decimal('total_shares', 12, 2)->default(0);
        $table->decimal('total_amount', 12, 2)->default(0);
        $table->string('status')->nullable();
        $table->timestamps();
    });

    Schema::dropIfExists('share_capital_transaction_tbls');
    Schema::create('share_capital_transaction_tbls', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('share_capital_account_id')->nullable();
        $table->string('type')->nullable();
        $table->decimal('shares', 12, 2)->nullable();
        $table->decimal('amount_per_share', 12, 2)->nullable();
        $table->decimal('total_amount', 12, 2)->nullable();
        $table->string('payment_method')->nullable();
        $table->string('reference_no')->nullable();
        $table->string('note')->nullable();
        $table->string('status')->nullable();
        $table->date('transaction_date')->nullable();
        $table->timestamps();
    });

    Schema::dropIfExists('savings_account_tbls');
    Schema::create('savings_account_tbls', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id')->nullable();
        $table->decimal('balance', 12, 2)->default(0);
        $table->decimal('interest_accrued_balance', 12, 2)->default(0);
        $table->date('interest_last_credited_at')->nullable();
        $table->timestamps();
    });

    Schema::dropIfExists('savings_transaction_tbls');
    Schema::create('savings_transaction_tbls', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('savings_account_id')->nullable();
        $table->string('type');
        $table->decimal('amount', 12, 2);
        $table->decimal('balance_after', 12, 2)->nullable();
        $table->string('payment_method')->nullable();
        $table->string('note')->nullable();
        $table->string('reference_no')->nullable();
        $table->date('transaction_date');
        $table->string('status')->default('completed');
        $table->timestamps();
    });
}

function sensitiveRouteUser(array $attrs = []): Users_tbl
{
    return Users_tbl::create(array_merge([
        'first_name' => 'Juan',
        'last_name' => 'Dela Cruz',
        'username' => 'juandelacruz',
        'email' => 'juan.delacruz@gmail.com',
        'password' => Hash::make('StrongPass1!'),
        'role' => 'Member',
    ], $attrs));
}

// ────────────────────────────────────────────────────────────────────────────
// ShareCapital::showForMember — no more auto-login as the URL member id
// ────────────────────────────────────────────────────────────────────────────

it('does not auto-login visitors who open a share-capital invitation link', function () {
    sensitiveRouteTables();
    $owner = sensitiveRouteUser(['id' => 5]);

    $this->get(route('share_capital.show', ['id' => $owner->id]))
        ->assertRedirect(route('login'));

    expect(auth()->check())->toBeFalse()
        ->and(auth()->id())->toBeNull();
});

it('blocks a different member from viewing another member share-capital form', function () {
    sensitiveRouteTables();
    $owner = sensitiveRouteUser();
    $other = sensitiveRouteUser(['username' => 'other.member', 'email' => 'other.member@gmail.com']);

    $this->actingAs($other)
        ->get(route('share_capital.show', ['id' => $owner->id]))
        ->assertForbidden();
});

it('lets the account owner view their own share-capital form', function () {
    sensitiveRouteTables();
    $owner = sensitiveRouteUser();
    \Illuminate\Support\Facades\DB::table('share_capital_account_tbls')->insert([
        'user_id' => $owner->id,
        'total_shares' => 10,
        'total_amount' => 2000,
        'status' => 'Active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($owner)
        ->get(route('share_capital.show', ['id' => $owner->id]))
        ->assertOk();
});

// ────────────────────────────────────────────────────────────────────────────
// Application form GET/POST — IDOR + anonymous email-overwrite (takeover)
// ────────────────────────────────────────────────────────────────────────────

it('blocks guests from opening an approved Member application form', function () {
    sensitiveRouteTables();
    $member = sensitiveRouteUser();

    $this->get(route('applicationForm', $member->id))->assertForbidden();
});

it('blocks a different member from viewing another member application form', function () {
    sensitiveRouteTables();
    $owner = sensitiveRouteUser();
    $other = sensitiveRouteUser(['username' => 'other.viewer', 'email' => 'other.viewer@gmail.com']);

    $this->actingAs($other)
        ->get(route('applicationForm', $owner->id))
        ->assertForbidden();
});

it('still allows a guest to self-serve the application form of a Pending applicant', function () {
    sensitiveRouteTables();
    $pending = sensitiveRouteUser(['role' => 'Pending']);

    $this->get(route('applicationForm', $pending->id))->assertOk();
});

it('blocks anonymous re-writing of an approved member application (email takeover attempt)', function () {
    sensitiveRouteTables();
    $member = sensitiveRouteUser(['id' => 9, 'email' => 'victim@gmail.com']);

    $this->post(route('applicationFormButton', $member->id), [
        'email' => 'attacker@example.com',
        'fullname' => 'Hacker Name',
    ])->assertForbidden();

    expect(Users_tbl::find($member->id)->email)->toBe('victim@gmail.com');
});

it('blocks a different member from over-writing another member application', function () {
    sensitiveRouteTables();
    $owner = sensitiveRouteUser(['id' => 3]);
    $other = sensitiveRouteUser(['username' => 'other.writer', 'email' => 'other.writer@gmail.com']);

    $this->actingAs($other)
        ->post(route('applicationFormButton', $owner->id), ['email' => 'swap@example.com'])
        ->assertForbidden();

    expect(Users_tbl::find($owner->id)->email)->not->toBe('swap@example.com');
});

it('still processes application submissions for a Pending applicant', function () {
    sensitiveRouteTables();
    $pending = sensitiveRouteUser(['role' => 'Pending', 'email' => 'pending.applicant@gmail.com']);

    $this->post(route('applicationFormButton', $pending->id), [
        'contact_no' => '0917 123 4567',
        'present_address' => 'Quezon City',
    ])->assertRedirect(route('applicationForm', $pending->id));

    $otherInfo = Otherinfo_tbl::where('user_id', $pending->id)->first();
    expect($otherInfo)->not->toBeNull()
        ->and($otherInfo->contact_no)->toBe('0917 123 4567');
});

// ────────────────────────────────────────────────────────────────────────────
// Savings receipt download — ownership scoping
// ────────────────────────────────────────────────────────────────────────────

it('requires authentication to download a savings receipt', function () {
    sensitiveRouteTables();
    $owner = sensitiveRouteUser();
    $account = savings_account_tbl::create(['user_id' => $owner->id, 'balance' => 100]);
    savings_transaction_tbl::create([
        'savings_account_id' => $account->id,
        'type' => 'deposit',
        'amount' => 100,
        'balance_after' => 100,
        'reference_no' => 'SRV-OWNER-001',
        'transaction_date' => now()->toDateString(),
        'status' => 'completed',
    ]);

    $this->get(route('savings.receipt', 'SRV-OWNER-001'))->assertRedirect(route('login'));
});

it('blocks members from downloading another member savings receipt', function () {
    sensitiveRouteTables();
    $owner = sensitiveRouteUser();
    $other = sensitiveRouteUser(['username' => 'receipt.other', 'email' => 'receipt.other@gmail.com']);
    $account = savings_account_tbl::create(['user_id' => $owner->id, 'balance' => 100]);
    savings_transaction_tbl::create([
        'savings_account_id' => $account->id,
        'type' => 'deposit',
        'amount' => 100,
        'balance_after' => 100,
        'reference_no' => 'SRV-OWNER-002',
        'transaction_date' => now()->toDateString(),
        'status' => 'completed',
    ]);

    $this->actingAs($other)
        ->get(route('savings.receipt', 'SRV-OWNER-002'))
        ->assertForbidden();
});

it('lets the account owner download their own savings receipt', function () {
    sensitiveRouteTables();
    $owner = sensitiveRouteUser();
    $account = savings_account_tbl::create(['user_id' => $owner->id, 'balance' => 100]);
    savings_transaction_tbl::create([
        'savings_account_id' => $account->id,
        'type' => 'deposit',
        'amount' => 100,
        'balance_after' => 100,
        'reference_no' => 'SRV-OWNER-003',
        'transaction_date' => now()->toDateString(),
        'status' => 'completed',
    ]);

    $this->actingAs($owner)
        ->get(route('savings.receipt', 'SRV-OWNER-003'))
        ->assertOk();
});

// ────────────────────────────────────────────────────────────────────────────
// Misc route-hardening regressions
// ────────────────────────────────────────────────────────────────────────────

it('gates the static admin preview page (all-user records) behind admin', function () {
    sensitiveRouteTables();

    $this->get('/static-page')->assertRedirect(route('login'));
});

it('keeps the member share-capital contribution route distinct from the admin route', function () {
    expect(route('share_capital.member.store'))->toBe(url('/share-capital-form'))
        ->and(route('share_capital.store.admin'))->toBe(url('/share-capital/store'));
});

it('requires a POST for the share-capital invitation action', function () {
    sensitiveRouteTables();

    $this->get(route('message.user', 1))->assertStatus(405);
});