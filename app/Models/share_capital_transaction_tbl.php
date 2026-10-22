<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class share_capital_transaction_tbl extends Model
{
    public $incrementing = true;

    protected $table = 'share_capital_transaction_tbls';

    protected $fillable = [
        'share_capital_account_id',
        'type',
        'shares',
        'amount_per_share',
        'total_amount',
        'payment_method',
        'reference_no',
        'gcash_proof_path',
        'gcash_number',
        'gcash_reference_no',
        'note',
        'status',
        'transaction_date',
        'void_reason',
        'voided_by',
        'voided_at',
        'created_by',
        'approved_by',
        'reviewed_by',
        'reviewed_at',
        'review_note',
    ];

    public function shareCapitalAccount()
    {
        return $this->belongsTo(share_capital_account_tbl::class, 'share_capital_account_id');
    }
}
