<?php

use App\Models\Users_tbl;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

// NOTE: phpunit.xml runs against sqlite :memory: and RefreshDatabase is
// disabled in tests/Pest.php, so every table a test touches must be created
// by hand here (drop-if-exists keeps each test isolated).

function settingsTables(): void
{
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

    Schema::dropIfExists('account_settings_tbls');
    Schema::create('account_settings_tbls', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id')->unique();
        $table->boolean('loan_reminders')->default(true);
        $table->boolean('savings_updates')->default(true);
        $table->boolean('email_digest')->default(false);
        $table->boolean('announcements')->default(true);
        $table->boolean('login_alerts')->default(true);
        $table->timestamps();
    });

    Schema::dropIfExists('resignation_requests_tbls');
    Schema::create('resignation_requests_tbls', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id')->nullable();
        $table->text('reason')->nullable();
        $table->string('status')->nullable();
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

    // Tables touched by the shared navbar composer (NavbarComposer →
    // buildMemberNotifications) when rendering the Settings page.
    Schema::dropIfExists('otherinfo_tbls');
    Schema::create('otherinfo_tbls', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id')->nullable();
        $table->string('contact_no')->nullable();
        $table->string('present_address')->nullable();
        $table->string('permanent_address')->nullable();
        $table->date('date_of_birth')->nullable();
        $table->string('place_of_birth')->nullable();
        $table->string('sex')->nullable();
        $table->string('civil_status')->nullable();
        $table->string('citizenship')->nullable();
        $table->string('blood_type')->nullable();
        $table->string('height')->nullable();
        $table->string('weight')->nullable();
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

    Schema::dropIfExists('lending_program_tbls');
    Schema::create('lending_program_tbls', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id')->nullable();
        $table->string('status')->nullable();
        $table->string('lending_type')->nullable();
        $table->date('due_date')->nullable();
        $table->decimal('monthly_payment', 12, 2)->nullable();
        $table->timestamps();
    });

    Schema::dropIfExists('lending_status_tbls');
    Schema::create('lending_status_tbls', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('lending_id')->nullable();
        $table->date('due_date')->nullable();
        $table->decimal('remaining_balance', 12, 2)->nullable();
        $table->decimal('total_payments', 12, 2)->nullable();
        $table->integer('payments_made')->nullable();
        $table->timestamps();
    });

    Schema::dropIfExists('share_capital_account_tbls');
    Schema::create('share_capital_account_tbls', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id')->nullable();
        $table->decimal('total_amount', 12, 2)->default(0);
        $table->timestamps();
    });

    Schema::dropIfExists('share_capital_transaction_tbls');
    Schema::create('share_capital_transaction_tbls', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('share_capital_account_id')->nullable();
        $table->string('type')->nullable();
        $table->string('status')->nullable();
        $table->decimal('total_amount', 12, 2)->nullable();
        $table->decimal('shares', 12, 2)->nullable();
        $table->string('reference_no')->nullable();
        $table->timestamps();
    });

    Schema::dropIfExists('savings_account_tbls');
    Schema::create('savings_account_tbls', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id')->nullable();
        $table->decimal('balance', 12, 2)->default(0);
        $table->timestamps();
    });

    Schema::dropIfExists('savings_transaction_tbls');
    Schema::create('savings_transaction_tbls', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('savings_account_id')->nullable();
        $table->string('type')->nullable();
        $table->string('status')->nullable();
        $table->decimal('amount', 12, 2)->nullable();
        $table->string('reference_no')->nullable();
        $table->timestamp('transaction_date')->nullable();
        $table->timestamps();
    });

    Schema::dropIfExists('time_deposits_tbl');
    Schema::create('time_deposits_tbl', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('savings_account_id')->nullable();
        $table->string('status')->nullable();
        $table->timestamp('opened_at')->nullable();
        $table->timestamp('maturity_date')->nullable();
        $table->timestamps();
    });

    Schema::dropIfExists('seminar_attendees_tbls');
    Schema::create('seminar_attendees_tbls', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id')->nullable();
        $table->unsignedBigInteger('seminar_id')->nullable();
        $table->string('status')->nullable();
        $table->timestamps();
    });

    Schema::dropIfExists('seminar_completions_tbls');
    Schema::create('seminar_completions_tbls', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id')->nullable();
        $table->boolean('pmes_completed')->default(false);
        $table->boolean('fundamentals_completed')->default(false);
        $table->boolean('finance_completed')->default(false);
        $table->timestamps();
    });
}

function settingsMember(): Users_tbl
{
    $user = Users_tbl::create([
        'first_name' => 'Jane',
        'last_name' => 'Member',
        'username' => 'jane',
        'email' => 'jane.member@gmail.com',
        'password' => bcrypt('secret'),
        'role' => 'member',
        'base_role' => 'member',
        'status' => 'Active',
    ]);
    $user->password_changed_at = now()->subDays(30);
    $user->save();

    return $user;
}

// ────────────────────────────────────────────────────────────────────────────
// Guests must be redirected to login for every settings-cluster route
// ────────────────────────────────────────────────────────────────────────────

it('redirects guests away from the settings page', function () {
    $this->get(route('Settings'))->assertRedirect(route('login'));
});

it('redirects guests away from the edit-profile page', function () {
    $this->get(route('EditProfileMember'))->assertRedirect(route('login'));
    $this->post(route('UpdateProfileMember'))->assertRedirect(route('login'));
});

it('redirects guests away from settings state-changing routes', function () {
    $this->post(route('settings.toggle'))->assertRedirect(route('login'));
    $this->post(route('settings.changePassword'))->assertRedirect(route('login'));
    $this->post(route('settings.requestDeactivation'))->assertRedirect(route('login'));
    $this->get(route('settings.export'))->assertRedirect(route('login'));
});

// ────────────────────────────────────────────────────────────────────────────
// Authenticated members keep full use of their own settings cluster
// ────────────────────────────────────────────────────────────────────────────

it('lets an authenticated member open the settings page', function () {
    settingsTables();

    $this->actingAs(settingsMember())
        ->get(route('Settings'))
        ->assertOk();
});

it('lets an authenticated member toggle a setting end-to-end', function () {
    settingsTables();

    $this->actingAs(settingsMember())
        ->post(route('settings.toggle'), ['field' => 'loan_reminders', 'value' => '1'])
        ->assertOk()
        ->assertJson([
            'success' => true,
            'field' => 'loan_reminders',
            'value' => true,
        ]);
});

it('lets an authenticated member change their password end-to-end', function () {
    settingsTables();
    $member = settingsMember();

    $this->actingAs($member)
        ->post(route('settings.changePassword'), [
            'current_password' => 'secret',
            'new_password' => 'NewStrong1!',
            'new_password_confirmation' => 'NewStrong1!',
        ])
        ->assertOk()
        ->assertJson(['success' => true, 'message' => 'Password updated successfully.']);

    expect(Hash::check('NewStrong1!', $member->fresh()->password))->toBeTrue();
});

it('lets an authenticated member request deactivation end-to-end', function () {
    settingsTables();
    $member = settingsMember();

    $this->actingAs($member)
        ->post(route('settings.requestDeactivation'), ['reason' => 'Moving abroad'])
        ->assertOk()
        ->assertJson(['success' => true]);

    expect(DB::table('resignation_requests_tbls')
        ->where('user_id', $member->id)
        ->where('status', 'Pending')
        ->exists())->toBeTrue();
});