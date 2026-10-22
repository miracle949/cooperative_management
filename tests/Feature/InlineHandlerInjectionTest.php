<?php

/**
 * Guards the inline-handler → data-attribute conversion applied across the
 * admin/member views. Every dynamic value that used to be interpolated into an
 * inline `on*="..."` handler is now delivered through `data-*` attributes and
 * read via `dataset`, which carries the escaped form of the value.
 *
 * The payloads here would previously break out of the handler literal and
 * execute `alert(1)` once the page loaded. We assert:
 *   1. No inline event handler in the rendered HTML contains `alert(`.
 *   2. The raw payload never appears unescaped anywhere in the document.
 *   3. The payload never ends up inside a <script> block.
 *   4. The payload IS present, but confined to its data-* attribute, and
 *      `html_entity_decode(ENT_QUOTES)` recovers the exact original value
 *      (mirroring how `dataset` sees it in the browser).
 *   5. The previous inline `onclick="..."` forms are gone.
 */

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

const INLINE_HT_SQ = "'); alert(1); //";
const INLINE_HT_TAG = '<img src=x onerror=alert(1)>';

function inlineCoreTables(): void
{
    $tables = [
        'users_tbls' => function (Blueprint $t) {
            $t->id();
            $t->string('first_name')->nullable();
            $t->string('last_name')->nullable();
            $t->string('email')->nullable();
            $t->string('username')->nullable();
            $t->string('password')->nullable();
            $t->string('role')->nullable();
            $t->string('base_role')->nullable();
            $t->string('status')->nullable();
            $t->text('sidebar_permissions')->nullable();
            $t->timestamps();
        },
        'otherinfo_tbls' => function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id');
            $t->string('contact_no')->nullable();
            $t->string('membership_category')->nullable();
            $t->string('profile_picture')->nullable();
            $t->text('skills')->nullable();
            $t->timestamps();
        },
        'roles' => function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->string('description')->nullable();
            $t->boolean('is_system')->default(false);
            $t->timestamps();
        },
        'savings_account_tbls' => function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id');
            $t->decimal('balance', 12, 2)->default(0);
            $t->string('status')->default('active');
            $t->decimal('interest_accrued_balance', 12, 2)->default(0);
            $t->date('opened_at')->nullable();
            $t->timestamps();
        },
        'savings_transaction_tbls' => function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id')->nullable();
            $t->unsignedBigInteger('savings_account_id')->nullable();
            $t->string('type')->nullable();
            $t->string('status')->nullable();
            $t->decimal('amount', 12, 2)->default(0);
            $t->decimal('balance_after', 12, 2)->default(0);
            $t->decimal('total_amount', 12, 2)->default(0);
            $t->string('reference_no')->nullable();
            $t->string('payment_method')->nullable();
            $t->string('gcash_proof_path')->nullable();
            $t->string('gcash_reference_no')->nullable();
            $t->string('gcash_number')->nullable();
            $t->string('withdrawal_payment_method')->nullable();
            $t->string('note')->nullable();
            $t->string('void_reason')->nullable();
            $t->boolean('approved')->default(false);
            $t->timestamp('transaction_date')->nullable();
            $t->timestamps();
        },
        'share_capital_account_tbls' => function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id');
            $t->decimal('total_amount', 12, 2)->default(0);
            $t->string('status')->default('active');
            $t->date('opened_at')->nullable();
            $t->timestamps();
        },
        'share_capital_transaction_tbls' => function (Blueprint $t) {
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
            $t->string('gcash_reference_no')->nullable();
            $t->string('gcash_number')->nullable();
            $t->string('gcash_proof_path')->nullable();
            $t->string('note')->nullable();
            $t->string('void_reason')->nullable();
            $t->timestamp('transaction_date')->nullable();
            $t->timestamps();
        },
        'lending_program_tbls' => function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id');
            $t->string('reference_no')->nullable();
            $t->string('lending_type')->nullable();
            $t->decimal('lending_amount', 12, 2)->default(0);
            $t->decimal('monthly_payment', 12, 2)->default(0);
            $t->decimal('total_payment', 12, 2)->default(0);
            $t->decimal('total_interest', 12, 2)->default(0);
            $t->string('status')->nullable();
            $t->timestamps();
        },
        'lending_status_tbls' => function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('lending_id');
            $t->decimal('remaining_balance', 12, 2)->default(0);
            $t->integer('payments_made')->default(0);
            $t->integer('total_payments')->default(0);
            $t->date('due_date')->nullable();
            $t->string('status')->nullable();
            $t->timestamps();
        },
        'seminar_attendees_tbls' => function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('seminar_id');
            $t->unsignedBigInteger('user_id');
            $t->string('status')->nullable();
            $t->timestamp('attended_at')->nullable();
            $t->timestamps();
        },
        'seminar_completions_tbls' => function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id');
            $t->boolean('pmes_completed')->default(false);
            $t->boolean('fundamentals_completed')->default(false);
            $t->boolean('finance_completed')->default(false);
            $t->timestamp('completed_at')->nullable();
            $t->timestamps();
        },
        'time_deposits_tbl' => function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('savings_account_id');
            $t->decimal('goal_amount', 12, 2)->nullable();
            $t->decimal('balance', 12, 2)->default(0);
            $t->decimal('interest_rate', 8, 2)->nullable();
            $t->integer('term_months')->nullable();
            $t->date('opened_at')->nullable();
            $t->date('maturity_date')->nullable();
            $t->string('status')->nullable();
            $t->string('reference_no')->nullable();
            $t->string('claim_reference_no')->nullable();
            $t->timestamp('claimed_at')->nullable();
            $t->decimal('claimed_amount', 12, 2)->nullable();
            $t->decimal('claimed_principal', 12, 2)->nullable();
            $t->decimal('claimed_interest', 12, 2)->nullable();
            $t->decimal('interest_accrued_balance', 12, 2)->default(0);
            $t->timestamp('last_interest_credited_at')->nullable();
            $t->timestamps();
        },
        'resignation_requests_tbls' => function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id');
            $t->string('status')->nullable();
            $t->boolean('withdraw_share_capital')->default(false);
            $t->boolean('is_released')->default(false);
            $t->timestamp('release_date')->nullable();
            $t->timestamps();
        },
        'family_tbls' => function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id');
            $t->string('spouse_name')->nullable();
            $t->date('spouse_date_birth')->nullable();
            $t->integer('number_son')->default(0);
            $t->integer('number_daughter')->default(0);
            $t->timestamps();
        },
        'membergovern_ids_tbls' => function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id');
            $t->string('sss_id')->nullable();
            $t->string('philhealth_id')->nullable();
            $t->string('pagibig_id')->nullable();
            $t->string('tin_id')->nullable();
            $t->timestamps();
        },
        'membervehi_tbls' => function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id');
            $t->string('vehicle_type')->nullable();
            $t->string('plate_no')->nullable();
            $t->integer('quantity')->default(0);
            $t->timestamps();
        },
        'allied_worker_assignments' => function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id');
            $t->string('previous_role')->nullable();
            $t->string('new_role')->nullable();
            $t->unsignedBigInteger('assigned_by')->nullable();
            $t->timestamp('assigned_at')->nullable();
            $t->string('status')->nullable();
            $t->string('reason')->nullable();
            $t->unsignedBigInteger('revoked_by')->nullable();
            $t->timestamp('revoked_at')->nullable();
            $t->timestamps();
        },
        'officers_tbls' => function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id');
            $t->string('position')->nullable();
            $t->date('term_start')->nullable();
            $t->date('term_end')->nullable();
            $t->integer('sort_order')->default(0);
            $t->timestamps();
        },
        'seminars_tbls' => function (Blueprint $t) {
            $t->id();
            $t->string('seminar_type')->nullable();
            $t->timestamp('schedule_datetime')->nullable();
            $t->string('delivery_type')->nullable();
            $t->string('online_link')->nullable();
            $t->string('exact_venue')->nullable();
            $t->string('meetup_place')->nullable();
            $t->timestamps();
        },
        'loan_settings_tbls' => function (Blueprint $t) {
            $t->id();
            $t->string('loan_type')->nullable();
            $t->boolean('is_active')->default(true);
            $t->decimal('min_share', 12, 2)->nullable();
            $t->decimal('handling_fee', 12, 2)->nullable();
            $t->timestamps();
        },
        'audit_logs' => function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id')->nullable();
            $t->string('admin_name')->nullable();
            $t->string('action')->nullable();
            $t->string('target_type')->nullable();
            $t->unsignedBigInteger('target_id')->nullable();
            $t->string('user_role')->nullable();
            $t->string('ip_address')->nullable();
            $t->text('details')->nullable();
            $t->text('old_values')->nullable();
            $t->text('new_values')->nullable();
            $t->timestamps();
        },
        'announcements_tbls' => function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id');
            $t->string('title');
            $t->text('content');
            $t->timestamps();
        },
        'announcement_comments_tbls' => function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('announcement_id');
            $t->unsignedBigInteger('user_id');
            $t->text('content');
            $t->timestamps();
        },
        'announcement_likes_tbls' => function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('announcement_id');
            $t->unsignedBigInteger('user_id');
            $t->timestamps();
        },
        'announcement_polls_tbls' => function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id');
            $t->string('question');
            $t->text('options');
            $t->timestamp('expires_at')->nullable();
            $t->timestamps();
        },
        'announcement_poll_votes_tbls' => function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('poll_id');
            $t->unsignedBigInteger('user_id');
            $t->unsignedInteger('option_index')->nullable();
            $t->timestamps();
        },
        'payment_methods_tbls' => function (Blueprint $t) {
            $t->id();
            $t->string('method_name');
            $t->boolean('has_qr_code')->default(false);
            $t->string('qr_code_image_path')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        },
        'dividend_rates_tbls' => function (Blueprint $t) {
            $t->id();
            $t->decimal('rate', 8, 4)->nullable();
            $t->integer('effective_year')->nullable();
            $t->timestamps();
        },
        'dividend_histories_tbls' => function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('share_capital_account_id');
            $t->decimal('dividend_amount', 12, 2)->default(0);
            $t->string('status')->nullable();
            $t->timestamp('date_paid')->nullable();
            $t->string('period_label')->nullable();
            $t->timestamps();
        },
        'savings_interest_settings_tbls' => function (Blueprint $t) {
            $t->id();
            $t->decimal('annual_rate', 8, 2)->default(2.00);
            $t->string('release_frequency')->default('quarterly');
            $t->decimal('min_balance_for_interest', 12, 2)->default(0);
            $t->decimal('maintaining_balance', 12, 2)->default(0);
            $t->timestamps();
        },
    ];

    foreach ($tables as $table => $closure) {
        Schema::dropIfExists($table);
        Schema::create($table, $closure);
    }
}

function inlineAdmin(array $attrs = []): App\Models\Users_tbl
{
    return inlineUser(array_merge([
        'first_name' => 'Main',
        'last_name' => 'Admin',
        'role' => 'admin',
        'status' => 'active',
    ], $attrs));
}

function inlineMember(array $attrs = []): App\Models\Users_tbl
{
    return inlineUser(array_merge([
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'role' => 'member',
        'status' => 'active',
    ], $attrs));
}

function inlineUser(array $attrs = []): App\Models\Users_tbl
{
    $allowed = [
        'first_name', 'last_name', 'email', 'username', 'password',
        'role', 'base_role', 'status', 'sidebar_permissions',
    ];

    $row = array_merge([
        'first_name' => $attrs['first_name'] ?? 'Test',
        'last_name' => $attrs['last_name'] ?? 'User',
        'email' => $attrs['email'] ?? null,
        'username' => $attrs['username'] ?? null,
        'password' => bcrypt('password'),
        'role' => $attrs['role'] ?? 'member',
        'base_role' => $attrs['base_role'] ?? null,
        'status' => $attrs['status'] ?? 'active',
        'sidebar_permissions' => $attrs['sidebar_permissions'] ?? null,
        'created_at' => now(),
        'updated_at' => now(),
    ], array_intersect_key($attrs, array_flip($allowed)));

    if (is_array($row['sidebar_permissions'])) {
        $row['sidebar_permissions'] = json_encode($row['sidebar_permissions']);
    }

    $id = DB::table('users_tbls')->insertGetId($row);

    return \App\Models\Users_tbl::find($id);
}

/**
 * Core payload-safety checks shared by every test.
 */
function inlineAssertSafe(string $html): void
{
    // 1. No inline event handler anywhere may contain executable payload code.
    preg_match_all('/\son[a-z0-9]+\s*=\s*"[^"]*\balert\s*\(/i', $html, $m);
    expect($m[0])->toBe([]);

    // 2. The raw payload strings must never appear unescaped in the document.
    expect(str_contains($html, INLINE_HT_SQ))->toBeFalse(
        'raw single-quote payload appeared unescaped in output'
    );
    expect(str_contains($html, '<img src=x onerror=alert(1)>'))->toBeFalse(
        'raw tag payload appeared unescaped in output'
    );

    // 3. The payload may never land inside a <script> block.
    preg_match_all('/<script[^>]*>(.*?)<\/script>/is', $html, $blocks);
    foreach ($blocks[1] as $block) {
        expect(str_contains($block, INLINE_HT_SQ))->toBeFalse('breakout payload present inside script block');
        expect(str_contains($block, '<img src=x'))->toBeFalse('tag payload present inside script block');
    }
}

/**
 * A `data-*` attribute must carry the escaped payload and decode back to the
 * exact original value (the browser's `dataset` does the same decode).
 */
function inlineAssertDataRoundTrip(string $html, string $attr, string $expectedRaw): void
{
    $pattern = '/' . preg_quote($attr, '/') . '="([^"]*)"(?=\s|>)/';
    expect(preg_match_all($pattern, $html, $m))->toBeGreaterThanOrEqual(
        1,
        "attribute {$attr} not found in rendered HTML"
    );
    $decoded = array_map(
        fn ($v) => html_entity_decode($v, ENT_QUOTES | ENT_HTML5),
        $m[1]
    );
    expect(in_array($expectedRaw, $decoded, true))
        ->toBeTrue("attribute {$attr} did not round-trip the payload");
}

function inlinePaginator(array $items, int $perPage, string $path, array $appends): LengthAwarePaginator
{
    $paginator = new LengthAwarePaginator(
        collect(array_slice($items, 0, $perPage)),
        count($items),
        $perPage,
        1,
        ['path' => $path]
    );
    $paginator->appends($appends);

    return $paginator;
}

function inlineSqliteYear(): void
{
    $pdo = DB::connection()->getPdo();
    if (! method_exists($pdo, 'sqliteCreateFunction')) {
        return;
    }
    $pdo->sqliteCreateFunction('YEAR', function ($date) {
        return $date ? (int) substr((string) $date, 0, 4) : null;
    });
    $pdo->sqliteCreateFunction('DATE_FORMAT', function ($date, $format) {
        if (! $date) {
            return null;
        }
        $slash = str_replace('/', '-', (string) $date);
        $parts = explode(' ', $slash);
        $ymd = explode('-', $parts[0]);
        $hms = isset($parts[1]) ? explode(':', $parts[1]) : [];
        $tokens = [
            '%Y' => $ymd[0] ?? '',
            '%y' => $ymd[0] ?? '',
            '%m' => $ymd[1] ?? '',
            '%d' => $ymd[2] ?? '',
            '%H' => $hms[0] ?? '00',
            '%i' => $hms[1] ?? '00',
            '%s' => $hms[2] ?? '00',
        ];
        if (strlen($tokens['%y']) === 4) {
            $tokens['%y'] = substr($tokens['%y'], 2);
        }

        return strtr((string) $format, $tokens);
    });
}

it('renders the pending-member application review without executable payload', function () {
    inlineCoreTables();

    $viewer = inlineAdmin();
    $pending = inlineUser([
        'first_name' => INLINE_HT_SQ,
        'last_name' => INLINE_HT_TAG,
        'role' => 'pending',
        'status' => 'pending',
        'email' => 'evil-app@example.com',
    ]);
    DB::table('otherinfo_tbls')->insertGetId([
        'user_id' => $pending->id,
        'contact_no' => '09171112222',
        'membership_category' => 'Investor Associate',
    ]);

    $html = $this->actingAs($viewer)->get('/dashboard-members')->assertOk()->getContent();

    inlineAssertSafe($html);

    // Old inline deactivate form removed along with the Actions column.
    expect($html)->not->toContain('onclick="event.stopPropagation();confirmDeleteAdmin');
    expect($html)->not->toContain('data-member-id="'.$pending->id.'"');

    // Pending rows open the review modal through the delegation layer ...
    expect($html)->toContain('data-action="openMemberReviewModal"');
    expect($html)->toContain('id="pendingDetailModal"');
    expect($html)->toContain('id="review-accept-form"');
    expect($html)->toContain('id="review-decline-form"');
    expect($html)->toContain('data-confirm="Are you sure you want to decline this application?');

    // ... and the application payload is only ever written via textContent, never inline.
    expect($html)->toContain("document.getElementById('review-name').textContent = fullName");
});

it('renders allied-workers revoke/edit-role controls without executable payload', function () {
    inlineCoreTables();

    DB::table('roles')->insert([
        ['name' => 'Main Admin', 'slug' => 'admin', 'description' => 'System', 'is_system' => true, 'created_at' => now(), 'updated_at' => now()],
        ['name' => 'Officer', 'slug' => 'officer', 'description' => 'System', 'is_system' => true, 'created_at' => now(), 'updated_at' => now()],
        [
            'name' => INLINE_HT_SQ,
            'slug' => 'field-officer',
            'description' => INLINE_HT_TAG,
            'is_system' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $viewer = inlineAdmin();
    $aw = inlineMember([
        'first_name' => INLINE_HT_SQ,
        'last_name' => INLINE_HT_TAG,
        'email' => 'aw@example.com',
        'base_role' => 'member',
        'role' => 'field-officer',
    ]);

    $html = $this->actingAs($viewer)->get('/allied-workers')->assertOk()->getContent();

    inlineAssertSafe($html);

    expect($html)->not->toContain('onclick="openRevoke');
    expect($html)->toContain('js-open-revoke');
    expect($html)->toContain('data-aw-id="'.$aw->id.'"');
    inlineAssertDataRoundTrip($html, 'data-aw-name', INLINE_HT_SQ . ' ' . INLINE_HT_TAG);

    expect($html)->not->toContain('onclick="openEditRole');
    expect($html)->not->toContain('onclick="confirmDeleteRole');
    expect($html)->toContain('js-edit-role');
    expect($html)->toContain('js-delete-role');
    inlineAssertDataRoundTrip($html, 'data-role-name', INLINE_HT_SQ);
    inlineAssertDataRoundTrip($html, 'data-role-description', INLINE_HT_TAG);
    inlineAssertDataRoundTrip($html, 'data-role-perms', '[]');
});

it('renders officers edit control without executable payload', function () {
    inlineCoreTables();

    $viewer = inlineAdmin();
    $officerUser = inlineMember(['email' => 'officer@example.com']);
    DB::table('officers_tbls')->insert([
        'user_id' => $officerUser->id,
        'position' => INLINE_HT_SQ,
        'term_start' => '2026-01-01',
        'term_end' => '2027-01-01',
        'sort_order' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $html = $this->actingAs($viewer)->get('/dashboard-officers-committees')->assertOk()->getContent();

    inlineAssertSafe($html);

    expect($html)->not->toContain('onclick="editOfficer');
    expect($html)->toContain('js-edit-officer');
    inlineAssertDataRoundTrip($html, 'data-officer-position', INLINE_HT_SQ);
});

it('renders finance settings payment-method controls without executable payload', function () {
    inlineCoreTables();

    $pm = new \App\Models\PaymentMethod([
        'id' => 3,
        'method_name' => INLINE_HT_SQ,
        'has_qr_code' => true,
        'qr_code_image_path' => INLINE_HT_TAG,
        'is_active' => true,
    ]);

    $html = view('admin_components.partials.finance_settings', [
        'paymentMethods' => collect([$pm]),
        'loanSettingsList' => collect(),
        'years' => [2024, 2025, 2026],
        'year' => 2026,
        'currentYear' => 2026,
        'savingsInterestSettings' => (object) [
            'annual_rate' => 2.00,
            'release_frequency' => 'quarterly',
            'min_balance_for_interest' => 500.00,
            'maintaining_balance' => 1000.00,
        ],
        'loanEligibilitySettings' => (object) [
            'savings_to_loan_enabled' => true,
            'savings_to_loan_ratio' => 2.0,
            'minimum_shares' => 50,
        ],
    ])->render();

    inlineAssertSafe($html);

    expect($html)->not->toContain('onclick="editPaymentMethod');
    expect($html)->not->toContain('onclick="deletePaymentMethod');
    expect($html)->toContain('js-edit-payment-method');
    expect($html)->toContain('js-delete-payment-method');
    inlineAssertDataRoundTrip($html, 'data-pm-name', INLINE_HT_SQ);
    expect($html)->toContain('data-pm-has-qr="true"');
});

it('renders patronage records edit and pagination without executable payload in URL or attrs', function () {
    inlineCoreTables();

    $member = inlineMember();
    $recorder = inlineAdmin();

    $records = [];
    for ($i = 1; $i <= 15; $i++) {
        $records[] = (object) [
            'id' => $i,
            'user' => (object) ['first_name' => $member->first_name, 'last_name' => $member->last_name],
            'recorder' => (object) ['first_name' => $recorder->first_name, 'last_name' => $recorder->last_name],
            'source' => $i === 1 ? INLINE_HT_SQ : "source {$i}",
            'description' => $i === 1 ? INLINE_HT_TAG : "desc {$i}",
            'amount' => 100 + $i,
            'created_at' => now(),
        ];
    }

    $paginator = inlinePaginator($records, 10, '/dashboard-financial-activity', [
        'tab' => 'patronage',
        'patronage_source' => INLINE_HT_SQ,
        'patronage_description' => INLINE_HT_TAG,
        'patronage_amount' => INLINE_HT_SQ,
    ]);

    $html = view('admin_components.patronage_records_partial', [
        'year' => 2026,
        'recordCount' => 15,
        'totalAmount' => 1600.00,
        'records' => $paginator,
        'allMembers' => collect([$member]),
    ])->render();

    inlineAssertSafe($html);

    expect($html)->not->toContain('onclick="editPatronageRecord');
    expect($html)->toContain('js-edit-patronage-record');
    inlineAssertDataRoundTrip($html, 'data-record-source', INLINE_HT_SQ);
    inlineAssertDataRoundTrip($html, 'data-record-description', INLINE_HT_TAG);

    // Pagination carries the payload inside the URL — percent-encoded, never executable.
    expect($html)->toContain('js-patronage-records-page');
    expect($html)->toContain('data-page-url="');
    expect($html)->toContain(rawurlencode(INLINE_HT_SQ));
    expect($html)->not->toContain('onclick="loadPatronageRecordsPage');
});

it('renders dividends pagination URLs without executable payload', function () {
    inlineCoreTables();

    $member = inlineMember();

    $rows = [];
    for ($i = 1; $i <= 15; $i++) {
        $rows[] = (object) [
            'id' => $i,
            'user' => (object) ['first_name' => $member->first_name, 'last_name' => $member->last_name],
            'share_capital_amount' => 1000.00,
            'recommended_amount' => 120.00,
            'approved_amount' => 120.00,
            'status' => 'pending',
        ];
    }

    $paginator = inlinePaginator($rows, 10, '/dashboard-financial-activity', [
        'tab' => 'dividends',
        'dividends_year' => INLINE_HT_SQ,
        'dividends_status' => INLINE_HT_TAG,
    ]);

    $html = view('admin_components.dividends_table_partial', [
        'year' => 2026,
        'dividends' => $paginator,
        'totalSumShareCapital' => 15000.00,
        'totalSumRecommended' => 1800.00,
        'totalSumApproved' => 1800.00,
    ])->render();

    inlineAssertSafe($html);

    expect($html)->toContain('js-dividends-page');
    expect($html)->toContain('data-page-url="');
    expect($html)->toContain(rawurlencode(INLINE_HT_SQ));
    expect($html)->not->toContain('onclick="loadDividendsPage');
});

it('renders patronage table pagination URLs without executable payload', function () {
    inlineCoreTables();

    $member = inlineMember();

    $rows = [];
    for ($i = 1; $i <= 15; $i++) {
        $rows[] = (object) [
            'id' => $i,
            'user' => (object) ['first_name' => $member->first_name, 'last_name' => $member->last_name],
            'total_patronage' => 500.00,
            'allocation_ratio' => 0.08,
            'amount' => 40.00,
            'status' => 'pending',
        ];
    }

    $paginator = inlinePaginator($rows, 10, '/dashboard-financial-activity', [
        'tab' => 'patronage',
        'patronage_year' => INLINE_HT_SQ,
    ]);

    $html = view('admin_components.patronage_table_partial', [
        'year' => 2026,
        'patronageDistributions' => $paginator,
        'totalSumPatronage' => 7500.00,
        'totalSumPatronageApproved' => 1800.00,
    ])->render();

    inlineAssertSafe($html);

    expect($html)->toContain('js-patronage-page');
    expect($html)->toContain('data-page-url="');
    expect($html)->toContain(rawurlencode(INLINE_HT_SQ));
    expect($html)->not->toContain('onclick="loadPatronagePage');
});

it('renders savings tab deposit/disburse rows without executable payload', function () {
    inlineCoreTables();

    $member = inlineMember([
        'first_name' => INLINE_HT_SQ,
        'last_name' => INLINE_HT_TAG,
    ]);

    $memberRow = (object) [
        'id' => $member->id,
        'first_name' => $member->first_name,
        'last_name' => $member->last_name,
        'otherinfo' => (object) ['contact_no' => '09170001111'],
    ];

    $account = (object) [
        'id' => 11,
        'balance' => 2500.00,
        'user' => $memberRow,
    ];

    $pendingDeposit = (object) [
        'id' => 21,
        'amount' => 500.00,
        'payment_method' => 'gcash',
        'gcash_reference_no' => 'REF123',
        'gcash_proof_path' => 'proofs/p.png',
        'gcash_number' => null,
        'created_at' => now(),
        'savingsAccount' => $account,
    ];

    $pendingWithdrawal = (object) [
        'id' => 31,
        'amount' => 300.00,
        'payment_method' => 'gcash',
        'gcash_reference_no' => 'WREF1',
        'gcash_proof_path' => null,
        'gcash_number' => '09178889999',
        'created_at' => now(),
        'savingsAccount' => $account,
    ];

    $html = view('admin_components.partials.finance_savings_tab', [
        'totalSavingsBalance' => 2500.00,
        'eligibleCount' => 1,
        'sirSettings' => (object) ['frequency_label' => 'Quarterly'],
        'pendingDeposits' => collect([$pendingDeposit]),
        'pendingWithdrawals' => collect([$pendingWithdrawal]),
        'savingsTransactions' => new LengthAwarePaginator(
            collect([]),
            0,
            10,
            1,
            ['path' => '/dashboard-financial-activity']
        ),
    ])->render();

    inlineAssertSafe($html);

    expect($html)->not->toContain('onclick="openDepositDetailModal');
    expect($html)->not->toContain('onclick="openWithdrawalDisburseModal');

    expect($html)->toContain('js-deposit-detail');
    inlineAssertDataRoundTrip($html, 'data-deposit-member', INLINE_HT_SQ . ' ' . INLINE_HT_TAG);
    inlineAssertDataRoundTrip($html, 'data-deposit-contact', '09170001111');

    expect($html)->toContain('js-disburse-withdrawal');
    inlineAssertDataRoundTrip($html, 'data-withdrawal-member', INLINE_HT_SQ . ' ' . INLINE_HT_TAG);
    inlineAssertDataRoundTrip($html, 'data-withdrawal-amount', '300.00');
    inlineAssertDataRoundTrip($html, 'data-withdrawal-contact', '09178889999');
});

it('renders member share-capital QR lightbox without executable payload', function () {
    inlineCoreTables();

    $member = inlineMember(['email' => 'member@example.com']);
    DB::table('payment_methods_tbls')->insert([
        'method_name' => 'GCash',
        'has_qr_code' => true,
        'qr_code_image_path' => INLINE_HT_TAG,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $html = $this->actingAs($member)->get('/share-capital')->assertOk()->getContent();

    inlineAssertSafe($html);

    expect($html)->not->toContain('onclick="openQrLightbox');
    expect($html)->toContain('js-open-qr-lightbox');
    inlineAssertDataRoundTrip($html, 'data-qr-src', asset('storage/' . INLINE_HT_TAG));
});

it('renders member savings QR lightbox without executable payload', function () {
    inlineCoreTables();
    inlineSqliteYear();

    $member = inlineMember(['email' => 'member2@example.com']);
    DB::table('payment_methods_tbls')->insert([
        'method_name' => 'GCash',
        'has_qr_code' => true,
        'qr_code_image_path' => INLINE_HT_SQ,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('savings_account_tbls')->insert([
        'user_id' => $member->id,
        'balance' => 0,
        'status' => 'active',
        'opened_at' => now()->toDateString(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $html = $this->actingAs($member)->get('/savings-page')->assertOk()->getContent();

    inlineAssertSafe($html);

    expect($html)->not->toContain('onclick="openQrLightbox');
    expect($html)->toContain('js-open-qr-lightbox');
    inlineAssertDataRoundTrip($html, 'data-qr-src', asset('storage/' . INLINE_HT_SQ));
});

it('renders member loan-status QR lightbox without executable payload', function () {
    inlineCoreTables();

    $member = inlineMember(['email' => 'member3@example.com']);
    DB::table('loan_settings_tbls')->insert([
        'loan_type' => 'Salary Loan',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('payment_methods_tbls')->insert([
        'method_name' => 'GCash',
        'has_qr_code' => true,
        'qr_code_image_path' => INLINE_HT_TAG,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $html = $this->actingAs($member)->get('/loan-status')->assertOk()->getContent();

    inlineAssertSafe($html);

    expect($html)->not->toContain('onclick="openQrLightbox');
    expect($html)->toContain('js-open-qr-lightbox');
    inlineAssertDataRoundTrip($html, 'data-qr-src', asset('storage/' . INLINE_HT_TAG));
});