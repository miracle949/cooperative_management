<?php

namespace App\Observers;

use App\Models\lending_program_tbl;
use App\Models\lending_status_tbl;

class LendingProgramObserver
{
    public function updated(lending_program_tbl $loan)
    {
        if ($loan->isDirty('status') && $loan->status === 'Approved') {

            $exists = lending_status_tbl::where('lending_id', $loan->id)->exists();
            if ($exists) {
                return;
            }

            $termMonths = (int) filter_var($loan->lending_type_term, FILTER_SANITIZE_NUMBER_INT);

            lending_status_tbl::create([
                'lending_id' => $loan->id,
                'user_id' => $loan->user_id,
                'remaining_balance' => $loan->total_payment,
                'total_paid' => 0,
                'payments_made' => 0,
                'total_payments' => $termMonths,
                'interest_rate' => \App\Models\Loan_settings_tbl::getRate($loan->lending_type),
                'due_date' => \Carbon\Carbon::parse($loan->created_at)
                    ->addDays(\App\Http\Controllers\lendingController::PAYMENT_INTERVAL_DAYS)
                    ->format('Y-m-d'),
                'status' => 'Active',
            ]);
        }
    }
}
