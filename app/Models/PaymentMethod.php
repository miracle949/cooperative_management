<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $table = 'payment_methods_tbls';

    protected $fillable = [
        'method_name',
        'has_qr_code',
        'qr_code_image_path',
        'is_active',
    ];

    protected $casts = [
        'has_qr_code' => 'boolean',
        'is_active' => 'boolean',
    ];
}
