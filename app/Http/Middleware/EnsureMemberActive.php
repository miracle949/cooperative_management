<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMemberActive
{
    /**
     * Keeps inactive members off the normal member pages.
     *
     * A member whose account is inactive (or has an in-flight reactivation
     * request) can only reach the dedicated inactive/re-activation page.
     * The page and the member.reactivate endpoint themselves are exempt from
     * this middleware so the self-service reactivation flow keeps working.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();

        if (! method_exists($user, 'isMemberBased') || ! $user->isMemberBased()) {
            return $next($request);
        }

        $status = strtolower((string) $user->status);
        $role = strtolower((string) $user->role);

        if ($status === 'inactive' || $status === 'reactivation_pending' || $role === 'inactive') {
            return redirect()->route('member.inactive');
        }

        return $next($request);
    }
}