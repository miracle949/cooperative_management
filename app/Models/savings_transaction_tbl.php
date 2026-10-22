<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class savings_transaction_tbl extends Model
{
    protected $table = 'savings_transaction_tbls';

    protected $fillable = [
        'savings_account_id',
        'type',
        'amount',
        'payment_method',
        'gcash_number',
        'gcash_reference_no',
        'balance_after',
        'note',
        'reference_no',
        'gcash_proof_path',
        'transaction_date',
        'status',
        'void_reason',
        'voided_by',
        'voided_at',
        'created_by',
        'approved_by',
        'reviewed_by',
        'reviewed_at',
        'review_note',
    ];

    public function savingsAccount()
    {
        return $this->belongsTo(savings_account_tbl::class, 'savings_account_id');
    }
}
