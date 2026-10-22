<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Notifications_tbl;
use App\Models\Otherinfo_tbl;
use App\Models\Role;
use App\Models\Users_tbl;
use App\Models\allied_worker_assignment_tbl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Allied Worker management: promote an existing member to an "Allied Worker"
 * role on the SAME users_tbls account (no duplicate account), plus secure mode
*  switching between the member and Allied Worker sides.
 */
class AlliedWorkerController extends Controller
{
    /**
     * Whether the current user may manage Allied Workers (GM or Main Admin).
     */
    private function canManage(): bool
    {
        $user = Auth::user();

        return $user && ($user->isGeneralManager() || $user->isMainAdmin());
    }

    private function deny(): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route('settings')->with('error', 'You are not authorized to manage Allied Workers.');
    }

    /**
     * List candidates (active members), current Allied Workers, active roles,
     * and assignment history.
     */
    public function index()
    {
        if (! $this->canManage()) {
            return $this->deny();
        }

        $activeRoles = Role::where('is_system', false)->orderBy('name')->get();
        $systemRoles = Role::where('is_system', true)->orderBy('name')->get();

        // Candidate members: member-based accounts that are currently regular members.
        $candidates = Users_tbl::where('status', 'active')
            ->where(function ($q) {
                $q->where('base_role', 'member')
                    ->whereIn(DB::raw('LOWER(role)'), ['member', 'pending', 'inactive']);
            })
            ->orWhere(function ($q) {
                $q->whereNull('base_role')->whereIn(DB::raw('LOWER(role)'), ['member', 'pending', 'inactive']);
            })
            ->orderBy('first_name')
            ->get();

        // Current Allied Workers: member-based accounts whose role indicates promotion.
        $alliedWorkers = Users_tbl::where('base_role', 'member')
            ->where(function ($q) {
                $q->whereNotIn(DB::raw('LOWER(role)'), ['member', 'pending', 'inactive']);
            })
            ->orderBy('first_name')
            ->get();

        $history = allied_worker_assignment_tbl::with(['user', 'assigner', 'revoker'])
            ->orderBy('created_at', 'desc')
            ->limit(200)
            ->get();

        // The General Manager is the top authority; only the General Manager
        // and custom roles remain after the removal of Admin and Officer.
        $roles = Role::orderBy('name')->get();

        $customRoleCount = Role::where('is_system', false)->count();

        // Active user counts per role (for the Manage Roles table).
        $roleCounts = [];
        foreach ($roles as $role) {
            $roleCounts[$role->slug] = Users_tbl::where('role', $role->slug)->count();
        }

        return view('admin_components.allied_workers', compact(
            'candidates',
            'alliedWorkers',
            'activeRoles',
            'systemRoles',
            'history',
            'roles',
            'customRoleCount',
            'roleCounts'
        ));
    }

    /**
     * Promote a member to an Allied Worker on the same account.
     */
    public function promote(Request $request)
    {
        if (! $this->canManage()) {
            return $this->deny();
        }

        $validated = $request->validate([
            'member_id' => 'required|exists:users_tbls,id',
            'role' => 'required|string|exists:roles,slug',
            'id_document' => 'required|file|mimes:jpg,jpeg,png,pdf|max:4096',
            'reason' => 'nullable|string|max:255',
        ]);

        $user = Users_tbl::findOrFail($validated['member_id']);

        // Only member-based accounts can be promoted.
        if (! $user->isMemberBased() && ! $user->isMember()) {
            return redirect()->route('allied-workers.index')
                ->with('error', 'Only existing member accounts can be promoted to Allied Worker.');
        }

        // Self-promotion is disallowed (GM/main admin acting on their own member self).
        if ((int) $user->id === (int) Auth::id()) {
            return redirect()->route('allied-workers.index')
                ->with('error', 'You cannot promote your own account.');
        }

        // The chosen role must be a custom, non-system role (an AW must not
        // inherit system privileges such as admin or general-manager).
        $role = Role::where('slug', $validated['role'])->firstOrFail();
        if ($role->is_system) {
            return redirect()->route('allied-workers.index')
                ->with('error', 'Allied Workers can only be assigned a custom (non-system) role.');
        }

        // Store the ID document on the private local disk (storage/app/private).
        $docPath = $request->file('id_document')->store('allied_worker_documents', 'local');

        // Snapshot the member's current membership category so it can be
        // restored if the Allied Worker role is later revoked.
        $previousCategory = Otherinfo_tbl::where('user_id', $user->id)->value('membership_category');

        allied_worker_assignment_tbl::create([
            'user_id' => $user->id,
            'previous_role' => $user->role,
            'previous_membership_category' => $previousCategory,
            'new_role' => $role->slug,
            'assigned_by' => Auth::id(),
            'assigned_at' => now(),
            'id_document_path' => $docPath,
            'status' => 'active',
            'reason' => $validated['reason'] ?? null,
        ]);

        // base_role stays 'member' so login routing + member portal remain intact.
        $user->update([
            'base_role' => 'member',
            'role' => $role->slug,
        ]);

        // The promoted account is labelled an Allied Worker by category too.
        Otherinfo_tbl::where('user_id', $user->id)->update(['membership_category' => 'Allied Workers']);

        // Notify the member about their promotion.
        Notifications_tbl::create([
            'user_id' => $user->id,
            'title' => 'Allied Worker Promotion',
            'message' => 'Congratulations! You have been promoted to Allied Worker ('.($role->name).') on your existing account. You can now perform staff duties from your account. Contact the General Manager for details.',
            'category' => 'inbox',
            'is_important' => true,
        ]);

        AuditLog::log(
            'Promoted Allied Worker',
            "Promoted member {$user->first_name} {$user->last_name} (ID: {$user->id}) to Allied Worker role '{$role->name}' ({$role->slug}) on the same account.",
            'user',
            $user->id
        );

        return redirect()->route('allied-workers.index')
            ->with('success', "Member promoted to Allied Worker as '{$role->name}'. Their existing account is unchanged and they can switch modes.");
    }

    /**
     * Revoke an Allied Worker back to a regular member on the same account.
     */
    public function revoke(Request $request)
    {
        if (! $this->canManage()) {
            return $this->deny();
        }

        $validated = $request->validate([
            'member_id' => 'required|exists:users_tbls,id',
            'reason' => 'nullable|string|max:255',
        ]);

        $user = Users_tbl::findOrFail($validated['member_id']);

        if (! $user->isAlliedWorker()) {
            return redirect()->route('allied-workers.index')
                ->with('error', 'This account is not currently an Allied Worker.');
        }

        // Close any active assignment record.
        $assignment = allied_worker_assignment_tbl::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        $previousCategory = $assignment?->previous_membership_category;

        allied_worker_assignment_tbl::where('user_id', $user->id)
            ->where('status', 'active')
            ->update([
                'status' => 'revoked',
                'revoked_by' => Auth::id(),
                'revoked_at' => now(),
                'reason' => $validated['reason'] ?? 'Revoked by management',
            ]);

        // Restore to the member base role; account is preserved.
        $user->update([
            'role' => 'member',
            'base_role' => 'member',
        ]);

        // Bring back the member's original membership category.
        Otherinfo_tbl::where('user_id', $user->id)->update(['membership_category' => $previousCategory]);

        // Clear any active AW session mode.
        session()->forget('aw_mode');

        Notifications_tbl::create([
            'user_id' => $user->id,
            'title' => 'Allied Worker Revoked',
            'message' => 'Your Allied Worker access has been revoked. You are back to a regular member account ($'.($validated['reason'] ?? 'No reason provided').').',
            'category' => 'inbox',
            'is_important' => true,
        ]);

        AuditLog::log(
            'Revoked Allied Worker',
            "Revoked Allied Worker {$user->first_name} {$user->last_name} (ID: {$user->id}) back to member role. Reason: ".($validated['reason'] ?? 'n/a'),
            'user',
            $user->id
        );

        return redirect()->route('allied-workers.index')
            ->with('success', 'Allied Worker access revoked. The account has been restored to a regular member.');
    }

    /**
     * Return to member side (works for any member-based account). Used to
     * leave an Allied Worker mode session.
     */
    public function switchToMember()
    {
        $user = Auth::user();
        if (! $user || ! (method_exists($user, 'isMemberBased') && $user->isMemberBased())) {
            return redirect()->route('dashboard')->with('error', 'This account is not a member-based account.');
        }

        session()->forget('aw_mode');

        return redirect()->route('MemberPortal')->with('message', 'Switched to Member mode.');
    }

    /**
     * Switch a member-based account into Allied Worker (staff) mode. The role
     * badge is stored on the account, so switching simply means landing on the
     * admin dashboard with the AW role active. Password re-verification is
     * required for this security-sensitive transition.
     */
    public function switchToAw(Request $request)
    {
        if (!\Illuminate\Support\Facades\Hash::check($request->password, $request->user()->password)) {
            return back()->withErrors(['aw_password' => 'Incorrect password. Please try again.']);
        }
    
        $user = Auth::user();
        if (! $user || ! (method_exists($user, 'isMemberBased') && $user->isMemberBased())) {
            return redirect()->route('dashboard')->with('error', 'Only member-based accounts can switch to Allied Worker mode.');
        }

        if (! $user->isAlliedWorker()) {
            return redirect()->route('MemberPortal')->with('error', 'Your account is not an Allied Worker.');
        }

        $request->validate([
            'password' => 'required|string',
        ]);

        if (! \Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
            return redirect()->route('MemberPortal')->with('error', 'Incorrect password. Mode switch was not performed.');
        }

        session(['aw_mode' => true]);

        return redirect()->route('dashboard')->with('message', 'You are now working as an Allied Worker.');
    }
}
