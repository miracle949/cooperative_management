<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;

/**
 * Segregation-of-duties (self-processing) rules for financial transactions
 * handled by staff/Allied Workers.
 *
 * The core idea: the person who CREATES/enters a transaction must NOT be the
 * person who FINALIZES it (approve/complete/disburse/void/reject). When a
 * blocked action occurs, the transaction stays Pending so that the General
 * Manager (or any other authorized staff member) can complete or void it
 * directly from the Finance page.
 */
class SoDGuard
{
    /**
     * Permissions that must never be delegated to custom roles, and can only be
     * granted by the main admin or the General Manager.
     */
    public static function protectedPermissions(): array
    {
        return ['role-management', 'settings', 'finance'];
    }

    /**
     * Is the given user (default current) the General Manager?
     */
    public static function isGeneralManager($user = null): bool
    {
        $u = $user ?? auth()->user();
        if (! $u) {
            return false;
        }

        return strtolower($u->role) === 'general-manager';
    }

    /**
     * Is the current authenticated user acting in a staff capacity?
     *
     * This is true for real admin/staff accounts (base_role=null) and for
     * Allied Workers. It is false for ordinary member accounts.
     */
    public static function actingAsStaff($user = null): bool
    {
        $u = $user ?? auth()->user();
        if (! $u) {
            return false;
        }

        if (method_exists($u, 'isMemberBased') && $u->isMemberBased()) {
            // Allied Workers hold a staff role on the same (member-based)
            // account and are subject to segregation-of-duties like any other
            // staff member. Plain members are not.
            return method_exists($u, 'isAlliedWorker') && $u->isAlliedWorker();
        }

        return true;
    }

    /**
     * Whether this actor may finalize (approve/complete/disburse/void/reject)
     * the given transaction under the segregation-of-duties rule.
     *
     * The GM may always finalize. Any other staff actor who created the
     * transaction themselves may not.
     */
    public static function canFinalize(object $transaction): bool
    {
        if (self::isGeneralManager()) {
            return true;
        }

        if (! self::actingAsStaff()) {
            return true;
        }

        if ($transaction->created_by && (int) $transaction->created_by === (int) Auth::id()) {
            return false;
        }

        return true;
    }

    /**
     * Equivalent to canFinalize but returns the JSON-appropriate denial so the
     * action writers can respond uniformly. The transaction stays Pending so
     * the GM or another authorized staff member can process it from the
     * Finance page.
     */
    public static function denialMessage(): array
    {
        return [
            'success' => false,
            'message' => 'You cannot finalize a transaction you created yourself (segregation of duties). Leave it Pending — the General Manager can complete it from the Finance page.',
        ];
    }
}
