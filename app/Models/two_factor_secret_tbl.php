<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * TOTP two-factor-authentication configuration per privileged user.
 *
 * The TOTP secret is stored using Laravel's encrypted cast (remains
 * ciphertext-at-rest). Recovery codes are stored only as bcrypt hashes;
 * the plaintext values are shown to the owner exactly once, immediately
 * after generation (enrollment or regeneration).
 */
class two_factor_secret_tbl extends Model
{
    protected $table = 'two_factor_secrets_tbls';

    protected $fillable = [
        'user_id',
        'secret',
        'recovery_codes',
        'confirmed_at',
        'enabled',
    ];

    protected $casts = [
        'secret' => 'encrypted',
        'recovery_codes' => 'encrypted:array',
        'confirmed_at' => 'datetime',
        'enabled' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(Users_tbl::class, 'user_id');
    }
}