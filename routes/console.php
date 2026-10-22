<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('savings:release-interest')
    ->monthlyOn(1, '00:10')
    ->timezone('Asia/Manila')
    ->withoutOverlapping();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('savings:process')->dailyAt('00:05');

// One-time backfill: capitalize previously-accrued savings interest into the
// main balance. Stop scheduling it once it has run successfully (marker file).
$backfillMarker = storage_path('framework/interest_capitalize_backfill_done');
if (! file_exists($backfillMarker)) {
    Schedule::command('savings:capitalize-backfill')
        ->dailyAt('00:06')
        ->after(function () use ($backfillMarker) {
            file_put_contents($backfillMarker, now()->toDateTimeString());
        });
}

// Schedule::call(function () {
//     app(\App\Services\LoanPenaltyService::class)->applyPenaltiesForAllOverdueLoans();
// })->dailyAt('01:00');

// Schedule::command('loans:process-overdue')->dailyAt('01:30');
