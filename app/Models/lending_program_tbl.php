<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class lending_program_tbl extends Model
{
    public $incrementing = true;

    protected $table = 'lending_program_tbls';

    protected $fillable = [
        'user_id',
        'reference_no',
        'lending_type',
        'lending_amount',
        'lending_type_term',

        // Loan Charges (Computed Amounts)
        'processing_fee_rate',
        'service_fee_rate',
        'loan_protection_fee',
        'retention_paid_rate',
        'retention_unpaid_rate',

        // Amount Received by Borrower
        'net_proceeds',
        'net_proceeds_adjustment_type',

        // Payment Information
        'monthly_income',
        'monthly_payment',
        'total_payment',
        'total_interest',

        'purpose_loan',
        'status',
        'decline_reason',
        'due_date',

        // Shared
        'valid_id',
        'proof_of_income',

        // Emergency
        'proof_of_emergency',

        // Business
        'business_permit',
        'financial_statement',

        // Education
        'school_id',
        'cor',
        'cog',
    ];

    public function user()
    {
        return $this->belongsTo(Users_tbl::class, 'user_id');
    }

    public function repayments()
    {
        return $this->hasMany(lending_repayments_tbl::class, 'lending_id');
    }
}
