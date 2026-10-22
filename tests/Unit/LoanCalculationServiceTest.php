<?php

use App\Services\LoanCalculationService;

test('diminishing balance: 10,000 / 12 months / 2% ties out to 11,300 total', function () {
    $service = new LoanCalculationService();
    $schedule = $service->buildSchedule(10000.00, 2.00, 12);

    $installments = $schedule['installments'];
    $sumPrincipal = array_sum(array_column($installments, 'principal_due'));
    $sumInterest = array_sum(array_column($installments, 'interest_due'));
    $sumAmount = array_sum(array_column($installments, 'amount_due'));

    $cents = fn ($amount) => (int) round($amount * 100);

    expect($schedule['total_interest'])->toBe(1300.00);
    expect($schedule['total_payment'])->toBe(11300.00);
    expect($cents($sumPrincipal))->toBe(1000000);
    expect($cents($sumInterest))->toBe(130000);
    expect($cents($sumAmount))->toBe(1130000);
    expect($installments[count($installments) - 1]['balance_after'])->toBe(0.0);
});

test('final principal installment absorbs rounding so sum equals principal exactly', function () {
    $service = new LoanCalculationService();
    $schedule = $service->buildSchedule(10000.00, 2.00, 12);

    $installments = $schedule['installments'];

    for ($i = 0; $i < 11; $i++) {
        expect($installments[$i]['principal_due'])->toBe(833.33);
    }
    expect($installments[11]['principal_due'])->toBe(833.37);
    expect((int) round(array_sum(array_column($installments, 'principal_due')) * 100))->toBe(1000000);
});

test('interest declines every period (diminishing balance)', function () {
    $service = new LoanCalculationService();
    $schedule = $service->buildSchedule(10000.00, 2.00, 12);

    $interests = array_column($schedule['installments'], 'interest_due');
    expect($interests[0])->toBe(200.00);
    expect($interests[11])->toBe(16.67);

    for ($i = 1; $i < count($interests); $i++) {
        expect($interests[$i])->toBeLessThanOrEqual($interests[$i - 1]);
    }
});

test('6 months / 6000 / 2%: equal principal, declining interest, exact totals', function () {
    $service = new LoanCalculationService();
    $schedule = $service->buildSchedule(6000.00, 2.00, 6);

    $installments = $schedule['installments'];

    expect(array_column($installments, 'principal_due'))->toBe([1000.00, 1000.00, 1000.00, 1000.00, 1000.00, 1000.00]);
    expect(array_column($installments, 'interest_due'))->toBe([120.00, 100.00, 80.00, 60.00, 40.00, 20.00]);
    expect($schedule['total_interest'])->toBe(420.00);
    expect($schedule['total_payment'])->toBe(6420.00);
});

test('single month loan: interest = principal x monthly rate', function () {
    $service = new LoanCalculationService();
    $schedule = $service->buildSchedule(1000.00, 2.00, 1);

    expect($schedule['total_interest'])->toBe(20.00);
    expect($schedule['total_payment'])->toBe(1020.00);
    expect($schedule['installments'][0]['principal_due'])->toBe(1000.00);
    expect($schedule['installments'][0]['interest_due'])->toBe(20.00);
});

test('add-fees-back: fees are an additive liability that never touch interest math', function () {
    // ₱1,000 / 6 months / 2% diminishing balance + ₱112 total fee spread evenly.
    $service = new LoanCalculationService();
    $schedule = $service->buildSchedule(1000.00, 2.00, 6, null, 112.00);

    $installments = $schedule['installments'];

    // Interest is computed on the ₱1,000 principal ONLY — the fee must not grow it.
    expect($schedule['total_interest'])->toBe(70.00);
    expect(array_column($installments, 'interest_due'))->toBe([20.00, 16.67, 13.33, 10.00, 6.67, 3.33]);

    // Fees are tracked separately and sum exactly to the ₱112 total.
    expect($schedule['total_fee'])->toBe(112.00);
    expect((int) round(array_sum(array_column($installments, 'fee_due')) * 100))->toBe(11200);

    // Total payable = principal + interest + fees = ₱1,182.00.
    expect($schedule['total_payment'])->toBe(1182.00);

    // Average monthly payment = total / term = ₱197.00.
    expect($service->averageMonthlyPayment($schedule))->toBe(197.00);

    // Installments still decrease and the loan fully amortizes.
    $amounts = array_column($installments, 'amount_due');
    for ($i = 1; $i < count($amounts); $i++) {
        expect($amounts[$i])->toBeLessThan($amounts[$i - 1]);
    }
    expect($installments[count($installments) - 1]['balance_after'])->toBe(0.0);
});

test('add-fees-back: principal still absorbs rounding so sum equals principal exactly', function () {
    $service = new LoanCalculationService();
    $schedule = $service->buildSchedule(1000.00, 2.00, 6, null, 112.00);

    $installments = $schedule['installments'];

    for ($i = 0; $i < 5; $i++) {
        expect($installments[$i]['principal_due'])->toBe(166.67);
    }
    expect($installments[5]['principal_due'])->toBe(166.65);
    expect((int) round(array_sum(array_column($installments, 'principal_due')) * 100))->toBe(100000);

    // Final fee installment absorbs the cent difference too.
    for ($i = 0; $i < 5; $i++) {
        expect($installments[$i]['fee_due'])->toBe(18.67);
    }
    expect($installments[5]['fee_due'])->toBe(18.65);
    expect((int) round(array_sum(array_column($installments, 'fee_due')) * 100))->toBe(11200);
});
