<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;

class PasswordResetController extends Controller
{
    /**
     * Generic, non-enumerating banner shown after every email submission,
     * whether or not the email exists.
     */
    private const GENERIC_EMAIL_STATUS = 'If an account exists for that email, a password reset link is on its way.';

    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = trim($request->input('email'));

        try {
            $status = Password::sendResetLink(['email' => $email]);

            if ($status === Password::RESET_LINK_SENT) {
                Log::info('Password reset link sent', ['email' => $email]);
            } else {
                // passwords.user      -> no row in the users table with that email
                // passwords.throttled -> requested again within the broker throttle window (default 60s)
                Log::warning('Password reset link NOT sent', ['email' => $email, 'status' => $status]);
            }
        } catch (\Throwable $e) {
            // SMTP / mail config / missing password_reset_tokens table, etc.
            Log::error('Password reset mail failed', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
        }

        // The user still sees the same generic message (no email enumeration).
        return back()->with('status', self::GENERIC_EMAIL_STATUS);
    }

    public function showResetForm(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function reset(Request $request)
    {
        $credentials = $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset(
            $credentials,
            function ($user, $password) {
                $user->password = bcrypt($password);
                $user->password_changed_at = now();
                $user->save();

                try {
                    AuditLog::log(
                        'Password Reset',
                        "User #{$user->id} reset their password via email link",
                        'user',
                        $user->id
                    );
                } catch (\Exception $e) {
                    // Non-fatal: the password was already changed successfully.
                }
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', 'Your password has been reset. Please sign in with your new password.');
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'This password reset link is invalid or has expired.']);
    }
}