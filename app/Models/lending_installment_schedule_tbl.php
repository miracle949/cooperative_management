<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class lending_installment_schedule_tbl extends Model
{
    public $incrementing = true;

    protected $table = 'lending_installment_schedules_tbls';

    protected $fillable = [
        'lending_id',
        'payment_number',
        'due_date',
        'principal_due',
        'interest_due',
        'fee_due',
        'fee_paid',
        'amount_due',
        'balance_after',
        'principal_paid',
        'interest_paid',
        'amount_paid',
        'is_paid',
    ];

    public function lending()
    {
        return $this->belongsTo(lending_program_tbl::class, 'lending_id');
    }
}
