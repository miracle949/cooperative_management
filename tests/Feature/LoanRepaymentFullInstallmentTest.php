<?php

use App\Models\Users_tbl;
use App\Models\lending_program_tbl;
use App\Models\lending_status_tbl;
use App\Services\LoanCalculationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;

// NOTE: phpunit.xml runs against sqlite :memory:. RefreshDatabase is disabled
// in tests/Pest.php, so every table this test touches must be created by hand
// here (drop-if-exists keeps each test isolated).

beforeEach(function () {
    Schema::dropIfExists('users_tbls');
    Schema::dropIfExists('lending_program_tbls');
    Schema::dropIfExists('lending_status_tbls');
    Schema::dropIfExists('lending_installment_schedules_tbls');
    Schema::dropIfExists('lending_repayments_tbls');
    Schema::dropIfExists('loan_settings_tbls');
    Schema::dropIfExists('audit_logs');
    Schema::dropIfExists('payment_methods_tbls');
    Schema::dropIfExists('share_capital_transaction_tbls');
    Schema::dropIfExists('savings_transaction_tbls');

    Schema::create('payment_methods_tbls', function (Blueprint $table) {
        $table->id();
        $table->string('method_name');
        $table->boolean('has_qr_code')->default(false);
        $table->string('qr_code_image_path')->nullable();
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });

    DB::table('payment_methods_tbls')->insert(['method_name' => 'Cash', 'is_active' => true]);
    DB::table('payment_methods_tbls')->insert(['method_name' => 'GCash', 'is_active' => true]);

    Schema::create('loan_settings_tbls', function (Blueprint $table) {
        $table->id();
        $table->string('loan_type')->unique();
        $table->decimal('interest_rate', 6, 2)->default(2.00);
        $table->decimal('late_fee_percentage', 6, 2)->default(2.00);
        $table->unsignedInteger('grace_period_months')->default(1);
        $table->timestamps();
    });

    Schema::create('users_tbls', function (Blueprint $table) {
        $table->id();
        $table->string('first_name')->nullable();
        $table->string('last_name')->nullable();
        $table->string('email')->nullable();
        $table->string('password')->nullable();
        $table->string('role')->default('Member');
        $table->string('status')->nullable();
        $table->timestamps();
    });

    Schema::create('lending_program_tbls', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->string('lending_type')->nullable();
        $table->decimal('lending_amount', 12, 2)->default(0);
        $table->string('lending_type_term')->nullable();
        $table->decimal('monthly_payment', 12, 2)->default(0);
        $table->decimal('total_payment', 12, 2)->default(0);
        $table->decimal('total_interest', 12, 2)->default(0);
        $table->string('status')->default('Pending');
        $table->timestamps();
    });

    Schema::create('lending_status_tbls', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('lending_id');
        $table->unsignedBigInteger('user_id');
        $table->decimal('remaining_balance', 12, 2)->default(0);
        $table->decimal('penalty_amount', 12, 2)->default(0);
        $table->date('last_penalty_date')->nullable();
        $table->decimal('total_paid', 12, 2)->default(0);
        $table->unsignedInteger('payments_made')->default(0);
        $table->unsignedInteger('total_payments')->default(0);
        $table->decimal('interest_rate', 6, 2)->default(0);
        $table->date('due_date')->nullable();
        $table->string('status')->default('Active');
        $table->timestamps();
    });

    Schema::create('lending_installment_schedules_tbls', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('lending_id');
        $table->unsignedInteger('payment_number');
        $table->date('due_date');
        $table->decimal('principal_due', 10, 2)->default(0);
        $table->decimal('interest_due', 10, 2)->default(0);
        $table->decimal('fee_due', 10, 2)->default(0);
        $table->decimal('fee_paid', 10, 2)->default(0);
        $table->decimal('amount_due', 10, 2)->default(0);
        $table->decimal('balance_after', 10, 2)->default(0);
        $table->decimal('principal_paid', 10, 2)->default(0);
        $table->decimal('interest_paid', 10, 2)->default(0);
        $table->decimal('amount_paid', 10, 2)->default(0);
        $table->boolean('is_paid')->default(false);
        $table->timestamps();
    });

    Schema::create('lending_repayments_tbls', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('lending_id');
        $table->unsignedBigInteger('user_id')->nullable();
        $table->unsignedInteger('payment_number')->default(0);
        $table->unsignedInteger('payment_sequence')->default(1);
        $table->decimal('amount_due', 12, 2)->default(0);
        $table->decimal('amount_paid', 12, 2)->default(0);
        $table->decimal('principal_paid', 12, 2)->default(0);
        $table->decimal('interest_paid', 12, 2)->default(0);
        $table->decimal('service_fee_paid', 12, 2)->default(0);
        $table->decimal('late_fee', 12, 2)->nullable();
        $table->dateTime('penalty_applied_at')->nullable();
        $table->date('due_date')->nullable();
        $table->date('payment_date')->nullable();
        $table->string('payment_method')->nullable();
        $table->string('payment_type')->nullable();
        $table->string('reference_no')->nullable();
        $table->string('payment_proof_path')->nullable();
        $table->text('notes')->nullable();
        $table->unsignedBigInteger('recorded_by')->nullable();
        $table->string('status', 20)->nullable()->default('Completed');
        $table->string('gcash_number', 20)->nullable();
        $table->string('gcash_reference_no', 20)->nullable()->unique();
        $table->string('void_reason', 100)->nullable();
        $table->unsignedBigInteger('voided_by')->nullable();
        $table->timestamp('voided_at')->nullable();
        $table->timestamps();
    });

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

    // GCash cross-table duplicate-reference check in lendingController::storeRepayment
    // queries gcash_reference_no + status on these two tables.
    Schema::create('share_capital_transaction_tbls', function (Blueprint $table) {
        $table->id();
        $table->string('gcash_reference_no', 32)->nullable();
        $table->string('status', 20)->nullable()->default('Completed');
        $table->timestamps();
    });

    Schema::create('savings_transaction_tbls', function (Blueprint $table) {
        $table->id();
        $table->string('gcash_reference_no', 32)->nullable();
        $table->string('status', 20)->nullable()->default('Completed');
        $table->timestamps();
    });

    DB::table('loan_settings_tbls')->insert([
        'loan_type' => 'Personal Loan',
        'interest_rate' => 2.00,
        'late_fee_percentage' => 2.00,
        'grace_period_months' => 1,
    ]);
});

function repaymentMember(): Users_tbl
{
    return Users_tbl::create([
        'first_name' => 'Test',
        'last_name' => 'Member',
        'email' => 'member@test.local',
        'password' => 'secret',
        'role' => 'Member',
        'status' => 'Active',
    ]);
}

/**
 * Create a 10,000 / 12-month / 2% loan using the real buildSchedule math
 * (installment #1 = 1,033.33 → interest 200.00 + principal 833.33).
 */
function createScheduleLoan(array $statusOverrides = []): array
{
    $member = repaymentMember();
    $loanCalc = new LoanCalculationService();

    $loan = lending_program_tbl::create([
        'user_id' => $member->id,
        'lending_amount' => 10000.00,
        'lending_type_term' => '12 months',
        'monthly_payment' => 941.67,
        'total_payment' => 11300.00,
        'total_interest' => 1300.00,
        'status' => 'Approved',
    ]);

    $schedule = $loanCalc->buildSchedule(10000.00, 2.0, 12);
    $loanCalc->persistSchedule($loan->id, $schedule);

    $status = lending_status_tbl::create(array_merge([
        'lending_id' => $loan->id,
        'user_id' => $member->id,
        'remaining_balance' => 11300.00,
        'total_paid' => 0,
        'payments_made' => 0,
        'total_payments' => 12,
        'interest_rate' => 2.00,
        'due_date' => Carbon::today()->addDays(5)->format('Y-m-d'),
        'status' => 'Active',
    ], $statusOverrides));

    return [$loan, $status, $member, $loanCalc];
}

function repay(array $params): \Illuminate\Testing\TestResponse
{
    return test()->post(route('repayment.store'), array_merge([
        'lending_id' => $params['lending_id'],
        'amount_paid' => $params['amount_paid'],
        'payment_method' => 'Cash',
    ], $params['extra'] ?? []));
}

it('TEST 1: exact full installment creates Pending record — no balance changes', function () {
    [$loan, $status, $member] = createScheduleLoan();
    $this->actingAs($member);

    $response = repay(['lending_id' => $loan->id, 'amount_paid' => 1033.33]);
    $response->assertSessionHasNoErrors();
    $response->assertSessionMissing('error');

    expect(DB::table('lending_repayments_tbls')->count())->toBe(1);
    $repayment = DB::table('lending_repayments_tbls')->first();
    expect($repayment->status)->toBe('Pending');
    expect((float) $repayment->amount_due)->toBe(1033.33);
    expect((float) $repayment->amount_paid)->toBe(1033.33);
    expect((float) $repayment->principal_paid)->toBe(0.0);
    expect((float) $repayment->interest_paid)->toBe(0.0);
    expect((float) $repayment->late_fee)->toBe(0.0);

    $row = DB::table('lending_installment_schedules_tbls')->where('payment_number', 1)->first();
    expect((bool) $row->is_paid)->toBeFalse();
    expect((float) $row->amount_paid)->toBe(0.0);

    $status->refresh();
    expect($status->payments_made)->toBe(0);
    expect((float) $status->total_paid)->toBe(0.0);
    expect((float) $status->remaining_balance)->toBe(11300.00);
    expect($status->status)->toBe('Active');
});

it('TEST 2: partial payment (500) is rejected with no writes', function () {
    [$loan, $status, $member] = createScheduleLoan();
    $this->actingAs($member);

    $response = repay(['lending_id' => $loan->id, 'amount_paid' => 500]);
    $response->assertRedirect();
    $response->assertSessionHas('error', 'Partial payments are not allowed. Please pay the full current installment of ₱1,033.33.');

    expect(DB::table('lending_repayments_tbls')->count())->toBe(0);
    expect(DB::table('lending_installment_schedules_tbls')->where('is_paid', true)->count())->toBe(0);
    $status->refresh();
    expect($status->payments_made)->toBe(0);
    expect((float) $status->remaining_balance)->toBe(11300.00);
});

it('TEST 3: another partial payment (800) is rejected with no writes', function () {
    [$loan, $status, $member] = createScheduleLoan();
    $this->actingAs($member);

    $response = repay(['lending_id' => $loan->id, 'amount_paid' => 800]);
    $response->assertRedirect();
    $response->assertSessionHas('error', 'Partial payments are not allowed. Please pay the full current installment of ₱1,033.33.');

    expect(DB::table('lending_repayments_tbls')->count())->toBe(0);
    $status->refresh();
    expect($status->payments_made)->toBe(0);
});

it('TEST 4: excess payment (2,000) is rejected — no rollover into the schedule', function () {
    [$loan, $status, $member] = createScheduleLoan();
    $this->actingAs($member);

    $response = repay(['lending_id' => $loan->id, 'amount_paid' => 2000]);
    $response->assertRedirect();
    $response->assertSessionHas('error', 'Payment exceeds the current installment. Please enter exactly ₱1,033.33.');

    expect(DB::table('lending_repayments_tbls')->count())->toBe(0);
    expect(DB::table('lending_installment_schedules_tbls')->where('is_paid', true)->count())->toBe(0);
    $status->refresh();
    expect($status->payments_made)->toBe(0);
    expect((float) $status->remaining_balance)->toBe(11300.00);
});

it('TEST 5: Pending payment does not advance the schedule — still on installment #1', function () {
    [$loan, $status, $member, $loanCalc] = createScheduleLoan();
    $this->actingAs($member);

    expect(repay(['lending_id' => $loan->id, 'amount_paid' => 1033.33]))->assertSessionHasNoErrors();

    // Schedule row NOT updated — deferred until admin confirms
    $row1 = DB::table('lending_installment_schedules_tbls')->where('payment_number', 1)->first();
    expect((bool) $row1->is_paid)->toBeFalse();

    $next = $loanCalc->currentInstallment($loan->id);
    expect($next)->not->toBeNull();
    expect($next->payment_number)->toBe(1);

    $status->refresh();
    expect($status->payments_made)->toBe(0);
});

it('TEST 6: legacy loan (no schedule rows) creates Pending record — no balance changes', function () {
    $member = repaymentMember();
    $this->actingAs($member);

    $loan = lending_program_tbl::create([
        'user_id' => $member->id,
        'lending_amount' => 10000.00,
        'lending_type_term' => '12 months',
        'monthly_payment' => 941.67,
        'total_payment' => 11300.00,
        'total_interest' => 1300.00,
        'status' => 'Approved',
    ]);

    lending_status_tbl::create([
        'lending_id' => $loan->id,
        'user_id' => $member->id,
        'remaining_balance' => 11300.00,
        'total_paid' => 0,
        'payments_made' => 0,
        'total_payments' => 12,
        'interest_rate' => 2.00,
        'due_date' => Carbon::today()->addDays(5)->format('Y-m-d'),
        'status' => 'Active',
    ]);

    $response = repay(['lending_id' => $loan->id, 'amount_paid' => 500, 'extra' => ['payment_number' => 1]]);
    $response->assertSessionHasNoErrors();

    expect(DB::table('lending_repayments_tbls')->count())->toBe(1);
    $repayment = DB::table('lending_repayments_tbls')->first();
    expect($repayment->status)->toBe('Pending');
    expect((float) $repayment->amount_paid)->toBe(500.00);

    $status = lending_status_tbl::where('lending_id', $loan->id)->first();
    expect($status->payments_made)->toBe(0);
    expect((float) $status->remaining_balance)->toBe(11300.00);
});

it('late fee: required total creates Pending record with penalty — no balance changes', function () {
    [$loan, $status, $member, $loanCalc] = createScheduleLoan([
        'due_date' => Carbon::today()->subDays(10)->format('Y-m-d'),
        'last_penalty_date' => null,
    ]);
    $this->actingAs($member);

    $installment = $loanCalc->currentInstallment($loan->id);
    $penalty = round((float) $installment->amount_due * 0.02, 2); // 20.67
    $requiredTotal = round((float) $installment->amount_due + $penalty, 2); // 1,054.00

    // Paying just the installment (without the late fee) is rejected.
    $response = repay(['lending_id' => $loan->id, 'amount_paid' => (float) $installment->amount_due]);
    $response->assertRedirect();
    $response->assertSessionHas(
        'error',
        "Partial payments are not allowed. Please pay the full current installment of ₱" . number_format($requiredTotal, 2)
            . " (includes ₱" . number_format($penalty, 2) . " late fee)."
    );
    expect(DB::table('lending_repayments_tbls')->count())->toBe(0);

    // Paying the full required total creates a Pending record.
    expect(repay(['lending_id' => $loan->id, 'amount_paid' => $requiredTotal]))->assertSessionHasNoErrors();

    $repayment = DB::table('lending_repayments_tbls')->first();
    expect($repayment->status)->toBe('Pending');
    expect((float) $repayment->amount_paid)->toBe((float) $installment->amount_due);
    expect((float) $repayment->late_fee)->toBe($penalty);

    // Schedule row NOT updated — deferred
    $row = DB::table('lending_installment_schedules_tbls')->where('payment_number', 1)->first();
    expect((bool) $row->is_paid)->toBeFalse();
    expect((float) $row->amount_paid)->toBe(0.0);

    $status->refresh();
    expect((float) $status->penalty_amount)->toBe(0.0);
    expect($status->last_penalty_date)->toBeNull();
    expect((float) $status->remaining_balance)->toBe(11300.00);
});

it('gcash-style payment: full required total accepted as Pending (deferred settlement)', function () {
    [$loan, $status, $member, $loanCalc] = createScheduleLoan([
        'due_date' => Carbon::today()->subDays(10)->format('Y-m-d'),
        'last_penalty_date' => null,
    ]);
    $this->actingAs($member);

    $installment = $loanCalc->currentInstallment($loan->id);
    $penalty = round((float) $installment->amount_due * 0.02, 2);
    $requiredTotal = round((float) $installment->amount_due + $penalty, 2);

    $this->post(route('repayment.store'), [
        'lending_id' => $loan->id,
        'amount_paid' => $requiredTotal,
        'payment_method' => 'GCash',
        'gcash_number' => '09171234567',
        'gcash_reference_no' => 'GCASH12345678',
        'gcash_proof' => \Illuminate\Http\UploadedFile::fake()->image('proof.jpg'),
        'extra' => [],
    ])->assertSessionHasNoErrors();

    $repayment = DB::table('lending_repayments_tbls')->first();
    expect($repayment->status)->toBe('Pending');
    expect($repayment->payment_method)->toBe('GCash');
    expect((float) $repayment->late_fee)->toBe($penalty);
    expect((float) $repayment->amount_paid)->toBe((float) $installment->amount_due);

    // Schedule row NOT yet updated — settlement deferred to admin confirmation
    expect((float) DB::table('lending_installment_schedules_tbls')->where('payment_number', 1)->value('amount_paid'))
        ->toBe(0.0);
});

/**
 * Create a ₱1,000 / 6-month / 2% loan with fees added back into the schedule
 * (feeAmount = ₱112 = 20 processing + 20 service + 12 protection + 60 retention).
 * Installment #1 = ₱205.34 (166.67 principal + 20 interest + 18.67 fee).
 */
function createScheduleLoanWithFees(array $statusOverrides = []): array
{
    $member = repaymentMember();
    $loanCalc = new LoanCalculationService();

    $loan = lending_program_tbl::create([
        'user_id' => $member->id,
        'lending_amount' => 1000.00,
        'lending_type_term' => '6 months',
        'monthly_payment' => 197.00,
        'total_payment' => 1182.00,
        'total_interest' => 70.00,
        'status' => 'Approved',
    ]);

    $schedule = $loanCalc->buildSchedule(1000.00, 2.0, 6, null, 112.00);
    $loanCalc->persistSchedule($loan->id, $schedule);

    $status = lending_status_tbl::create(array_merge([
        'lending_id' => $loan->id,
        'user_id' => $member->id,
        'remaining_balance' => 1182.00,
        'total_paid' => 0,
        'payments_made' => 0,
        'total_payments' => 6,
        'interest_rate' => 2.00,
        'due_date' => Carbon::today()->addDays(5)->format('Y-m-d'),
        'status' => 'Active',
    ], $statusOverrides));

    return [$loan, $status, $member, $loanCalc, $schedule];
}

it('add-fees-back: installment 1 carries principal + interest + fee portion', function () {
    [$loan, $status, $member, $loanCalc, $schedule] = createScheduleLoanWithFees();
    $this->actingAs($member);

    $row1 = DB::table('lending_installment_schedules_tbls')->where('payment_number', 1)->first();
    expect((float) $row1->principal_due)->toBe(166.67);
    expect((float) $row1->interest_due)->toBe(20.00);
    expect((float) $row1->fee_due)->toBe(18.67);
    expect((float) $row1->amount_due)->toBe(205.34);

    expect($schedule['total_interest'])->toBe(70.00);
    expect($schedule['total_fee'])->toBe(112.00);
    expect($schedule['total_payment'])->toBe(1182.00);
    expect($loanCalc->averageMonthlyPayment($schedule))->toBe(197.00);
});

it('add-fees-back: full repayment settles every installment and fee_paid sums to exactly ₱112', function () {
    [$loan, $status, $member, $loanCalc] = createScheduleLoanWithFees();
    $this->actingAs($member);

    // Pay each installment in full via the real submit + admin confirmation path.
    $rows = DB::table('lending_installment_schedules_tbls')
        ->where('lending_id', $loan->id)
        ->orderBy('payment_number')
        ->get();

    foreach ($rows as $row) {
        $installmentAmount = (float) $row->amount_due;

        // Member submits the exact full installment → Pending record.
        expect(repay(['lending_id' => $loan->id, 'amount_paid' => $installmentAmount]))
            ->assertSessionHasNoErrors();

        // Admin confirms → allocates interest→fee→principal into the schedule.
        $allocation = $loanCalc->allocatePayment($loan->id, $installmentAmount);
        expect($allocation['allocated'])->toBeTrue();
    }

    $scheduleRows = DB::table('lending_installment_schedules_tbls')
        ->where('lending_id', $loan->id)
        ->orderBy('payment_number')
        ->get();

    expect($scheduleRows->every(fn ($r) => (bool) $r->is_paid))->toBeTrue();

    $sumPrincipal = round($scheduleRows->sum(fn ($r) => (float) $r->principal_paid), 2);
    $sumInterest = round($scheduleRows->sum(fn ($r) => (float) $r->interest_paid), 2);
    $sumFee = round($scheduleRows->sum(fn ($r) => (float) $r->fee_paid), 2);

    expect($sumPrincipal)->toBe(1000.00);
    expect($sumInterest)->toBe(70.00);
    expect($sumFee)->toBe(112.00);
    expect(round($sumPrincipal + $sumInterest + $sumFee, 2))->toBe(1182.00);
    expect((float) $scheduleRows->last()->balance_after)->toBe(0.0);
});
