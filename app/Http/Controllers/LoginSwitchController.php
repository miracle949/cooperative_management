<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Users_tbl;
use App\Services\SingleSession;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LoginSwitchController extends Controller
{
    /* ───────── NEW device (not logged in) ───────── */

    public function status(Request $request)
    {
        $token = $request->session()->get('switch_token');
        if (!$token) {
            return response()->json(['state' => 'none']);
        }

        $row = DB::table('login_requests_tbls')->where('token', $token)->first();
        if (!$row) {
            $request->session()->forget('switch_token');
            return response()->json(['state' => 'none']);
        }

        $state = $row->status;

        if ($state === 'pending' && Carbon::parse($row->expires_at)->isPast()) {
            DB::table('login_requests_tbls')->where('id', $row->id)
                ->update(['status' => 'expired', 'updated_at' => now()]);
            $state = 'expired';
        }

        // Terminal states are reported once, then forgotten
        if (in_array($state, ['denied', 'expired', 'used'], true)) {
            $request->session()->forget('switch_token');
        }

        return response()->json(['state' => $state]);
    }

    public function complete(Request $request)
    {
        $token = $request->session()->get('switch_token');

        $row = $token ? DB::table('login_requests_tbls')
            ->where('token', $token)
            ->where('status', 'approved')
            ->where('expires_at', '>', now())
            ->first() : null;

        if (!$row) {
            return response()->json(['message' => 'Request is no longer valid.'], 422);
        }

        // Single use (atomic)
        $claimed = DB::table('login_requests_tbls')
            ->where('id', $row->id)->where('status', 'approved')
            ->update(['status' => 'used', 'updated_at' => now()]);

        if (!$claimed) {
            return response()->json(['message' => 'Request already used.'], 422);
        }

        $user = Users_tbl::find($row->user_id);
        $request->session()->forget('switch_token');

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->flash('just_logged_in', true);

        // Take over the lock with the NEW session id
        SingleSession::claim($user->id, $request->session()->getId());

        AuditLog::log(
            'Login Switch Completed',
            "User #{$user->id} switched sessions to a new device/browser (IP {$request->ip()}).",
            'user',
            $user->id
        );

        // UserHandle still runs your pending / reactivation / inactive checks
        return response()->json(['redirect' => route('UserHandle')]);
    }

    public function cancel(Request $request)
    {
        $token = $request->session()->pull('switch_token');
        if ($token) {
            DB::table('login_requests_tbls')
                ->where('token', $token)->where('status', 'pending')
                ->update(['status' => 'expired', 'updated_at' => now()]);
        }

        return response()->json(['ok' => true]);
    }

    /* ───────── CURRENT device (logged in = the owner) ───────── */

    public function pending()
    {
        $row = SingleSession::pendingFor(Auth::id());

        if (!$row) {
            return response()->json(['pending' => false]);
        }

        return response()->json([
            'pending' => true,
            'id'      => $row->id,
            'device'  => $row->user_agent,
            'ip'      => $row->ip,
            'when'    => Carbon::parse($row->created_at)->timezone('Asia/Manila')->format('M d, Y g:i A'),
        ]);
    }

    public function respond(Request $request)
    {
        $request->validate([
            'id'       => 'required|integer',
            'decision' => 'required|in:approve,deny',
        ]);

        $userId = Auth::id();

        $row = DB::table('login_requests_tbls')
            ->where('id', $request->id)
            ->where('user_id', $userId)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->first();

        if (!$row) {
            return response()->json(['message' => 'This request has expired.'], 410);
        }

        if ($request->decision === 'deny') {
            DB::table('login_requests_tbls')->where('id', $row->id)
                ->update(['status' => 'denied', 'updated_at' => now()]);

            AuditLog::log('Login Switch Denied', "User #{$userId} denied a login from IP {$row->ip}.", 'user', $userId);

            return response()->json(['ok' => true]);
        }

        // APPROVE: end THIS session first, then let the other device in
        SingleSession::release($userId);

        AuditLog::log('Login Switch Approved', "User #{$userId} approved a login from IP {$row->ip}.", 'user', $userId);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Flip to approved LAST so the new device can only proceed after the old session is gone
        DB::table('login_requests_tbls')->where('id', $row->id)->update([
            'status'     => 'approved',
            'expires_at' => now()->addSeconds(60), // window for the new device to finish
            'updated_at' => now(),
        ]);

        return response()->json(['redirect' => route('login')]);
    }
}