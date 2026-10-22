<?php

namespace App\Http\Middleware;

use App\Models\Users_tbl;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Additive protection on top of the existing routing/authorization layers.
 *
 * An eligible privileged user (General Manager / Main Admin) with confirmed
 * and enabled 2FA may not access the application unless their session has
 * been verified for this sign-in (session flag 2fa.verified === true).
 *
 * Non-eligible users and users who have not enabled 2FA pass through
 * untouched, so member behavior is not affected.
 */
class RequireTwoFactor
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user instanceof Users_tbl
            && method_exists($user, 'requiresTwoFactor')
            && $user->requiresTwoFactor()
            && method_exists($user, 'twoFactorEnabled')
            && $user->twoFactorEnabled()
            && ! $request->session()->get('2fa.verified', false)) {

            $routeName = $request->route()?->getName();

            // The challenge flow and a plain logout must always stay reachable
            // so an unverified user can verify or clearly abandon the session.
            if (! in_array($routeName, ['2fa.challenge', '2fa.challenge.attempt', 'logout'], true)) {
                return redirect()->route('2fa.challenge');
            }
        }

        return $next($request);
    }
}