<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class lending_repayments_tbl extends Model
{
    public $incrementing = true;

    protected $table = 'lending_repayments_tbls';

    protected $fillable = [
        'lending_id',
        'user_id',
        'payment_number',
        'payment_sequence',
        'amount_due',
        'amount_paid',
        'principal_paid',
        'interest_paid',
        'service_fee_paid',
        'due_date',
        'payment_date',
        'late_fee',
        'penalty_applied_at',
        'payment_method',
        'payment_type',
        'reference_no',
        'payment_proof_path',
        'notes',
        'recorded_by',
        'status',
        'gcash_number',
        'gcash_reference_no',
        'void_reason',
        'voided_by',
        'voided_at',
        'created_by',
        'approved_by',
        'reviewed_by',
        'reviewed_at',
        'review_note',
    ];

    public function lending()
    {
        return $this->belongsTo(lending_program_tbl::class, 'lending_id');
    }

    public function user()
    {
        return $this->belongsTo(Users_tbl::class, 'user_id');
    }
}
