<?php

use App\Models\Membergovern_ids_tbl;
use App\Models\Users_tbl;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

// U7 regression coverage for the two deferred U1 defects:
//   A. Registration (and other member-facing) `catch { dd(...) }` debug
//      termination that also swallowed validation errors.
//   B. Membergovern_ids_tbl::$fillable using *_no names that match neither
//      the schema (*_id columns) nor the data actually supplied, so uploaded
//      government IDs were silently mass-assignment-discarded.
//
// The table scaffolding (registrationTables) and payload builder
// (validRegistrationPayload) are shared from RegistrationAndOtpHardeningTest.

it('removes production debug termination from the registration path', function () {
    $registrationSource = file_get_contents(base_path('app/Http/Controllers/UsersHandle.php'));
    $loanSource = file_get_contents(base_path('app/Http/Controllers/lendingController.php'));

    expect($registrationSource)->not->toContain('dd(')
        ->and($registrationSource)->not->toContain('dump(')
        ->and($registrationSource)->not->toContain('die(')
        ->and($loanSource)->not->toContain('dd(')
        ->and($loanSource)->not->toContain('dump(');
});

it('returns normal validation errors for an invalid registration instead of exiting', function () {
    registrationTables();

    // Previously the registration's `catch (\Exception $e) { dd(...) }` caught
    // the ValidationException and hard-dumped the process. Now the framework's
    // default handling must redirect back with the visible validation errors.
    $this->post('/registration', [
        'first_name' => '',
        'last_name' => '',
        'email' => 'not-an-email',
        'password' => 'StrongPass1!',
        'password_confirmation' => 'StrongPass1!',
        'membership_category' => 'Operator',
        'civil_status' => 'Single',
    ])->assertSessionHasErrors(['first_name', 'last_name', 'email'])
        ->assertStatus(302);

    expect(Users_tbl::where('email', 'not-an-email')->exists())->toBeFalse();
});

it('persists all four uploaded government IDs from a new registration', function () {
    registrationTables();
    Storage::fake('public');

    $payload = validRegistrationPayload([
        'email' => 'gov.ids.member@gmail.com',
        'username' => 'govidsmember',
    ]);

    foreach (['sss_id', 'philhealth_id', 'pagibig_id', 'tin_id'] as $field) {
        $payload[$field] = UploadedFile::fake()->image($field.'.jpg')->size(150);
    }

    $this->post('/registration', $payload)->assertRedirect(route('RegisterPage'));

    $user = Users_tbl::where('email', 'gov.ids.member@gmail.com')->first();
    expect($user)->not->toBeNull();

    $row = DB::table('membergovern_ids_tbls')->where('user_id', $user->id)->first();
    expect($row)->not->toBeNull();

    foreach (['sss_id', 'philhealth_id', 'pagibig_id', 'tin_id'] as $field) {
        expect($row->$field)
            ->not->toBeNull()
            ->toContain('government_ids/');
    }

    // Distinct stored paths prove each upload reached its own column.
    expect(array_unique([
        $row->sss_id, $row->philhealth_id, $row->pagibig_id, $row->tin_id,
    ]))->toHaveCount(4);
});

it('allows mass assignment of government-ID fields that match the actual schema', function () {
    registrationTables();

    // Mirrors the exact array shape supplied by the registration/application
    // controllers. Before the fix these *_id keys were not fillable (*_no was)
    // and were silently dropped, so no columns were persisted.
    Membergovern_ids_tbl::create([
        'user_id' => 999,
        'sss_id' => 'gov_a',
        'philhealth_id' => 'gov_b',
        'pagibig_id' => 'gov_c',
        'tin_id' => 'gov_d',
    ]);

    $row = DB::table('membergovern_ids_tbls')->where('user_id', 999)->first();

    expect($row->sss_id)->toBe('gov_a')
        ->and($row->philhealth_id)->toBe('gov_b')
        ->and($row->pagibig_id)->toBe('gov_c')
        ->and($row->tin_id)->toBe('gov_d');
});

it('never writes government-ID file paths or identifiers into audit records', function () {
    registrationTables();
    Storage::fake('public');

    $payload = validRegistrationPayload([
        'email' => 'gov.audit.member@gmail.com',
        'username' => 'govauditmember',
        'sss_id' => UploadedFile::fake()->image('sss_id.jpg')->size(150),
    ]);

    $this->post('/registration', $payload)->assertRedirect(route('RegisterPage'));

    $user = Users_tbl::where('email', 'gov.audit.member@gmail.com')->first();

    $details = DB::table('audit_logs')
        ->where('action', 'User Registered')
        ->where('target_id', $user->id)
        ->value('details');

    expect($details)->not->toContain('government_ids/')
        ->and($details)->not->toContain('sss')
        ->and($details)->not->toContain('file');
});