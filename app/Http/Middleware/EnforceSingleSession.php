<?php

namespace App\Http\Middleware;

use App\Services\SingleSession;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EnforceSingleSession
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user && method_exists($user, 'isMemberBased') && $user->isMemberBased()) {
            $sid = $request->session()->getId();

            $row = DB::table('users_tbls')->where('id', $user->id)
                ->first(['current_session_id', 'last_activity_at']);

            if (SingleSession::activeElsewhere($user->id, $sid)) {
                // Another browser owns the live session → this one is not allowed in.
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'login' => 'This account is already logged in on another browser or device.',
                ]);
            }

            // Claim the slot, or refresh the heartbeat (at most once a minute).
            $stale = !$row || $row->current_session_id !== $sid || !$row->last_activity_at
                || now()->diffInSeconds($row->last_activity_at, true) >= 60;

            if ($stale) {
                SingleSession::claim($user->id, $sid);
            }
        }

        return $next($request);
    }
}