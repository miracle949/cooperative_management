<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Notifications_tbl;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SingleSession
{
    /** Minutes of inactivity before a session no longer blocks new logins. */
    const TIMEOUT_MINUTES = 15;

    /** Is this user active in a DIFFERENT browser session right now? */
    public static function activeElsewhere(int $userId, ?string $currentSessionId = null): bool
    {
        $row = DB::table('users_tbls')->where('id', $userId)
            ->first(['current_session_id', 'last_activity_at']);

        if (!$row || !$row->current_session_id || !$row->last_activity_at) {
            return false;
        }
        if ($currentSessionId && $row->current_session_id === $currentSessionId) {
            return false;
        }

        return Carbon::parse($row->last_activity_at)->gt(now()->subMinutes(self::TIMEOUT_MINUTES));
    }

    public static function claim(int $userId, string $sessionId): void
    {
        DB::table('users_tbls')->where('id', $userId)->update([
            'current_session_id' => $sessionId,
            'last_activity_at' => now(),
        ]);
    }

    public static function release(int $userId): void
    {
        DB::table('users_tbls')->where('id', $userId)->update([
            'current_session_id' => null,
            'last_activity_at' => null,
        ]);
    }

    /** Tell the account owner someone tried to get in, and log it. */
    public static function notifyBlockedAttempt($user, Request $request): void
    {
        $device = \Illuminate\Support\Str::limit((string) $request->userAgent(), 80, '…');
        $when = now()->timezone('Asia/Manila')->format('M d, Y g:i A');

        Notifications_tbl::create([
            'user_id' => $user->id,
            'title' => 'Login Attempt Blocked',
            'message' => "Someone tried to log in to your account from another browser/device on {$when} "
                . "(IP: {$request->ip()}, {$device}). It was blocked because you are already logged in. "
                . 'If this was not you, please change your password.',
            'category' => 'inbox',
            'is_important' => true,
        ]);

        AuditLog::log(
            'Blocked Concurrent Login',
            "Blocked a second login for user #{$user->id} from IP {$request->ip()} (account already active elsewhere).",
            'user',
            $user->id
        );
    }
}