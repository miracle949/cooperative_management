<?php

use App\Models\savings_account_tbl;
use App\Models\savings_transaction_tbl;
use App\Models\SavingsInterestRelease;
use App\Models\SavingsInterestSetting;
use App\Services\SavingsInterestService;
use App\Http\Controllers\SavingsController;
use App\Http\Controllers\ShareCapital;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;

// NOTE: phpunit.xml runs against sqlite :memory:. RefreshDatabase is disabled
// in tests/Pest.php, so every table this test touches must be created by hand
// here (drop-if-exists keeps each test isolated).

function savingsAccountTables()
{
    Schema::dropIfExists('savings_interest_releases_tbls');
    Schema::dropIfExists('savings_transaction_tbls');
    Schema::dropIfExists('savings_interest_settings_tbls');
    Schema::dropIfExists('savings_account_tbls');
    Schema::dropIfExists('audit_logs');

    Schema::create('savings_account_tbls', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id')->nullable();
        $table->decimal('balance', 12, 2)->default(0);
        $table->decimal('interest_accrued_balance', 12, 2)->default(0);
        $table->date('interest_last_credited_at')->nullable();
        $table->timestamps();
    });

    Schema::create('savings_transaction_tbls', function (Blueprint $table) {
        $table->id();
        $table->foreignId('savings_account_id');
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

    Schema::create('savings_interest_settings_tbls', function (Blueprint $table) {
        $table->id();
        $table->decimal('annual_rate', 5, 2)->default(2.00);
        $table->enum('release_frequency', ['monthly', 'quarterly', 'semi-annual', 'annual'])->default('quarterly');
        $table->decimal('min_balance_for_interest', 12, 2)->default(0.00);
        $table->decimal('maintaining_balance', 12, 2)->default(0.00);
        $table->timestamps();
    });

    SavingsInterestSetting::query()->delete();
    SavingsInterestSetting::create([
        'annual_rate' => 12.00,
        'release_frequency' => 'monthly',
        'min_balance_for_interest' => 0.00,
        'maintaining_balance' => 0.00,
    ]);

    Schema::create('savings_interest_releases_tbls', function (Blueprint $table) {
        $table->id();
        $table->foreignId('savings_account_id');
        $table->date('period_start');
        $table->date('period_end');
        $table->string('period_label');
        $table->decimal('amount', 12, 2);
        $table->string('reference_no', 64)->unique();
        $table->timestamps();
        $table->unique(['savings_account_id', 'period_start', 'period_end'], 'sir_acct_period_uniq');
    });

    Schema::create('audit_logs', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id')->nullable();
        $table->string('admin_name')->nullable();
        $table->string('ip_address', 45)->nullable();
        $table->string('user_role')->nullable();
        $table->string('action');
        $table->text('details')->nullable();
        $table->string('target_type')->nullable();
        $table->unsignedBigInteger('target_id')->nullable();
        $table->timestamps();
    });
}

function seedSavingsDeposit(int $accountId, float $amount, string $date, string $status = 'completed'): void
{
    $acct = savings_account_tbl::find($accountId);
    $newBalance = $status === 'completed' ? (float) $acct->balance + $amount : $acct->balance;
    if ($status === 'completed') {
        $acct->update(['balance' => $newBalance]);
    }
    savings_transaction_tbl::create([
        'savings_account_id' => $accountId,
        'type' => 'deposit',
        'amount' => $amount,
        'balance_after' => $newBalance,
        'payment_method' => 'GCash',
        'reference_no' => 'DEP-' . uniqid(),
        'transaction_date' => $date,
        'status' => $status,
    ]);
}

beforeEach(function () {
    savingsAccountTables();
});

it('capitalizes released interest into the balance and computeSavingsBalance counts it', function () {
    $acct = savings_account_tbl::create(['balance' => 0, 'interest_accrued_balance' => 0]);
    seedSavingsDeposit($acct->id, 1200, '2026-08-01'); // completed base
    $acct->refresh();

    $service = app(SavingsInterestService::class);
    $preBalance = (float) $acct->balance;
    $result = $service->releaseInterestForAccount(
        $acct,
        Carbon::parse('2026-08-01'),
        Carbon::parse('2026-08-31'),
        'Aug 2026'
    );

    expect($result)->not->toBeNull();
    $acct->refresh();

    // Interest landed in the main balance (compounding growth).
    expect((float) $acct->balance)->toBeGreaterThan($preBalance);
    expect((float) $acct->interest_accrued_balance)->toBe((float) $result['interest']);
    expect(round((float) $acct->balance, 2))->toBe(round($preBalance + (float) $result['interest'], 2));

    // The interest_credit row is completed with a correct balance_after snapshot.
    $tx = savings_transaction_tbl::where('savings_account_id', $acct->id)
        ->where('type', 'interest_credit')->first();
    expect($tx)->not->toBeNull();
    expect(strtolower((string) $tx->status))->toBe('completed');
    expect((float) $tx->balance_after)->toBe((float) $acct->balance);

    // computeSavingsBalance now includes interest, so it equals the stored balance.
    expect(round((float) (new SavingsController)->computeSavingsBalance($acct->id), 2))
        ->toBe(round((float) $acct->balance, 2));
});

it('compounds on the higher balance in the next period', function () {
    $acct = savings_account_tbl::create(['balance' => 0, 'interest_accrued_balance' => 0]);
    seedSavingsDeposit($acct->id, 1000, '2026-08-01');
    $acct->refresh();

    $service = app(SavingsInterestService::class);
    $service->releaseInterestForAccount($acct, Carbon::parse('2026-08-01'), Carbon::parse('2026-08-31'), 'Aug 2026');
    $acct->refresh();
    $afterFirst = (float) $acct->balance;

    // Next period: deposit nothing new; interest is recomputed on the grown balance.
    seedSavingsDeposit($acct->id, 500, '2026-09-01');
    $acct->refresh();
    $service->releaseInterestForAccount($acct, Carbon::parse('2026-09-01'), Carbon::parse('2026-09-30'), 'Sep 2026');
    $acct->refresh();
    $afterSecond = (float) $acct->balance;

    expect($afterSecond)->toBeGreaterThan($afterFirst + 500.00);
});

it('backfill is idempotent and does not double-count capitalized interest', function () {
    $acct = savings_account_tbl::create(['balance' => 0, 'interest_accrued_balance' => 0]);
    seedSavingsDeposit($acct->id, 1000, '2026-08-01');
    $acct->refresh();

    $service = app(SavingsInterestService::class);
    $service->releaseInterestForAccount($acct, Carbon::parse('2026-08-01'), Carbon::parse('2026-08-31'), 'Aug 2026');
    $acct->refresh();
    $capitalized = (float) $acct->balance;

    Artisan::call('savings:capitalize-backfill');
    $acct->refresh();
    expect(round((float) $acct->balance, 2))->toBe(round($capitalized, 2));

    // Second run is a no-op (idempotent).
    Artisan::call('savings:capitalize-backfill');
    $acct->refresh();
    expect(round((float) $acct->balance, 2))->toBe(round($capitalized, 2));
});

it('backfill folds legacy pre-capitalization interest_credit into the balance once', function () {
    // Simulate a pre-change state: interest parked in interest_accrued_balance,
    // balance never grew, and an interest_credit row exists with a stale balance_after.
    $acct = savings_account_tbl::create(['balance' => 0, 'interest_accrued_balance' => 50]);
    seedSavingsDeposit($acct->id, 1000, '2026-08-01');
    savings_transaction_tbl::create([
        'savings_account_id' => $acct->id,
        'type' => 'interest_credit',
        'amount' => 50,
        'balance_after' => 1000, // stale (pre-interest), as in legacy releases
        'payment_method' => 'System',
        'reference_no' => 'INT-LEGACY',
        'transaction_date' => '2026-08-31',
        'status' => 'completed',
    ]);

    Artisan::call('savings:capitalize-backfill');
    $acct->refresh();

    // Balance now includes the folded interest, exactly once.
    expect(round((float) $acct->balance, 2))->toBe(1050.00);

    // Interest row repaired to the correct capitalized snapshot.
    $tx = savings_transaction_tbl::where('savings_account_id', $acct->id)
        ->where('type', 'interest_credit')->first();
    expect(round((float) $tx->balance_after, 2))->toBe(1050.00);

    // Idempotent on a second run.
    Artisan::call('savings:capitalize-backfill');
    $acct->refresh();
    expect(round((float) $acct->balance, 2))->toBe(1050.00);
});