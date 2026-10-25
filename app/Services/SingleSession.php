<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Notifications_tbl;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SingleSession
{
    /** Minutes of inactivity before a session no longer blocks new logins. */
    const TIMEOUT_MINUTES = 5;

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

        // Stale lock: the session row was deleted (truncate, expiry, cleanup)
        if (
            config('session.driver') === 'database'
            && !DB::table('sessions')->where('id', $row->current_session_id)->exists()
        ) {
            self::release($userId);
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

    /** How long the owner has to answer a switch request. */
    const REQUEST_TTL_SECONDS = 120;

    /** Create (or reuse) a "please let me in" request and alert the owner. */
    public static function createSwitchRequest($user, Request $request): string
    {
        // Reuse this browser's still-pending request (e.g. user pressed Sign In twice)
        $existing = $request->session()->get('switch_token');
        if ($existing) {
            $row = DB::table('login_requests_tbls')
                ->where('token', $existing)
                ->where('user_id', $user->id)
                ->where('status', 'pending')
                ->where('expires_at', '>', now())
                ->first();
            if ($row) {
                return $existing;
            }
        }

        // Only one pending request per account at a time
        DB::table('login_requests_tbls')
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->update(['status' => 'expired', 'updated_at' => now()]);

        $token = Str::random(64);

        DB::table('login_requests_tbls')->insert([
            'user_id' => $user->id,
            'token' => $token,
            'status' => 'pending',
            'ip' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 250, ''),
            'expires_at' => now()->addSeconds(self::REQUEST_TTL_SECONDS),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $request->session()->put('switch_token', $token);

        // Backup in the notification list (title contains "login attempt" so it stays non-clickable)
        $device = Str::limit((string) $request->userAgent(), 80, '…');
        $when = now()->timezone('Asia/Manila')->format('M d, Y g:i A');

        Notifications_tbl::create([
            'user_id' => $user->id,
            'title' => 'Login Attempt Pending',
            'message' => "Someone tried to log in to your account on {$when} (IP: {$request->ip()}, {$device}). "
                . 'You can allow or deny it from the popup. If this was not you, deny it and change your password.',
            'category' => 'inbox',
            'is_important' => true,
        ]);

        AuditLog::log(
            'Login Switch Requested',
            "A second login for user #{$user->id} is waiting for approval (IP {$request->ip()}).",
            'user',
            $user->id
        );

        return $token;
    }

    /** The pending, unexpired request for this account (if any). */
    public static function pendingFor(int $userId)
    {
        return DB::table('login_requests_tbls')
            ->where('user_id', $userId)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();
    }
}