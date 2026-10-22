<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Users_tbl extends Authenticatable
{
    use Notifiable;
    protected $table = 'users_tbls';

    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'username',
        'email',
        'password',
        'role',
        'base_role',
        'status',
        'sidebar_permissions',
    ];

    protected $casts = [
        'sidebar_permissions' => 'array',
    ];

    protected $hidden = [
        'password',
    ];

    public function otherinfo()
    {
        return $this->hasOne(\App\Models\Otherinfo_tbl::class, 'user_id', 'id');
    }

    public function twoFactorSecret()
    {
        return $this->hasOne(\App\Models\two_factor_secret_tbl::class, 'user_id');
    }

    /**
     * Privileged accounts subject to the application's two-factor policy:
     * the General Manager and the Main Admin. Ordinary members, Allied
     * Workers, officers and other staff are NOT eligible unless their
     * existing role identity resolves to General Manager / Main Admin.
     */
    public function requiresTwoFactor(): bool
    {
        return $this->isGeneralManager() || $this->isMainAdmin();
    }

    /**
     * A user has 2FA active only when a confirmed enrollment exists.
     * Generating a secret without confirming it never activates 2FA.
     */
    public function twoFactorEnabled(): bool
    {
        try {
            // Fresh query: never rely on the memoized relation attribute, which
            // can hold a stale null when the record changes mid-session.
            $config = $this->twoFactorSecret()->first();
        } catch (\Throwable) {
            // The 2FA table is absent (e.g. pre-migration installs or
            // environments that do not provision the table): treat as off.
            return false;
        }

        return $config !== null
            && (bool) $config->enabled
            && $config->confirmed_at !== null
            && ! empty($config->secret);
    }

    public function getAllUser()
    {
        return $this->all();
    }

    public function savingsAccount()
    {
        return $this->hasOne(savings_account_tbl::class, 'member_id');
    }

    public function lendingPrograms()
    {
        return $this->hasMany(lending_program_tbl::class, 'member_id');
    }

    public function shareCapitalAccount()
    {
        return $this->hasOne(share_capital_account_tbl::class, 'user_id');
    }

    public function isMainAdmin(): bool
    {
        if ($this->isGeneralManager()) {
            return true;
        }

        if ($this->role !== 'admin') {
            return false;
        }
        $firstAdmin = self::where('role', 'admin')->orderBy('id')->first();

        return $firstAdmin && $this->id === $firstAdmin->id;
    }

    /**
     * Whether this account is an Allied Worker: a promoted member whose
     * role slug differs from their base member role, on the SAME account.
     */
    public function isAlliedWorker(): bool
    {
        if (empty($this->base_role)) {
            return false;
        }

        return ! in_array(strtolower($this->role), ['member', 'pending', 'inactive']);
    }

    public function isMember(): bool
    {
        return in_array(strtolower($this->role), ['member', 'pending', 'inactive']);
    }

    /**
     * Members eligible for admin member dropdowns: role-based members/pending
     * registrations plus promoted officer-accounts whose base_role is member
     * (Allied Workers). Excludes pure officer/executive accounts (GM etc.).
     */
    public function scopeMemberCandidates($query)
    {
        return $query->where(function ($q) {
            $q->whereIn('role', ['member', 'pending'])
                ->orWhere('base_role', 'member');
        });
    }

    /**
     * Whether this account belongs to a member regardless of the current
     * role badge. True for every member-based account (including Allied
     * Workers who were promoted on the same account — base_role stays member).
     */
    public function isMemberBased(): bool
    {
        if ($this->base_role === 'member') {
            return true;
        }

        return $this->isMember();
    }

    public function isGeneralManager(): bool
    {
        return strtolower($this->role) === 'general-manager';
    }

    /**
     * Whether the user's role grants access to the given sidebar module slug.
     *
     * Mirrors the inline logic in layouts/admin.blade.php:
     *  - null sidebar_permissions  → full access (system roles like GM)
     *  - empty array               → no sidebar access
     *  - populated array           → only listed keys
     */
    public function canAccessSidebar(string $slug): bool
    {
        if ($this->isGeneralManager()) {
            return true;
        }

        $role = Role::where('slug', $this->role)->first();
        if (! $role) {
            return false;
        }

        $perms = $role->sidebar_permissions;

        if (is_null($perms)) {
            return true;
        }

        return in_array($slug, $perms);
    }

    public function alliedWorkerAssignments()
    {
        return $this->hasMany(allied_worker_assignment_tbl::class, 'user_id');
    }

    /**
     * Ids of current Allied Workers (promoted members on the same account).
     * Used to hide their in-flight transactions from non-GM viewers.
     */
    public static function alliedWorkerIds(): array
    {
        return self::where('base_role', 'member')
            ->whereNotIn(\Illuminate\Support\Facades\DB::raw('LOWER(role)'), ['member', 'pending', 'inactive'])
            ->pluck('id')
            ->all();
    }
}
