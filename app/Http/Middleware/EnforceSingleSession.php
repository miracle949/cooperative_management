<?php

namespace App\Http\Middleware;

use App\Services\SingleSession;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EnforceSingleSession
{
    /** Background polls must not count as activity, or the 5-minute timeout never expires. */
    private array $passive = [
        'login-switch/pending',
        'member/status-ping',
    ];

    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Skip guests, and skip while a 2FA challenge is still pending
        if (!$user || $request->session()->has('2fa.pending_user_id')) {
            return $next($request);
        }

        $sid = $request->session()->getId();

        $row = DB::table('users_tbls')->where('id', $user->id)
            ->first(['current_session_id', 'last_activity_at']);

        if (SingleSession::activeElsewhere($user->id, $sid)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json([
                    'authenticated' => false,
                    'redirect' => route('login'),
                ], 401);
            }

            return redirect()->route('login')->withErrors([
                'login' => 'This account is already logged in on another browser or device.',
            ]);
        }

        // Claim the slot, or refresh the heartbeat (at most once a minute)
        $isPassive = $request->is($this->passive);

        $needsClaim = !$row
            || $row->current_session_id !== $sid
            || !$row->last_activity_at;

        $needsRefresh = !$isPassive
            && $row
            && now()->diffInSeconds($row->last_activity_at, true) >= 60;

        if ($needsClaim || $needsRefresh) {
            SingleSession::claim($user->id, $sid);
        }

        return $next($request);
    }
}