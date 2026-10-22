<?php

namespace App\Http\Middleware;

use App\Services\SoDGuard;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsStaff
{
    /**
     * Staff gate for administrative routes.
     *
     * Requires an authenticated account acting in a staff capacity
     * (admin/officer/general-manager accounts and Allied Workers).
     * Plain members are not granted administrative access here; the
     * existing controller-level checks (isMainAdmin, SoDGuard, etc.)
     * continue to apply for finer-grained actions.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        if (! SoDGuard::actingAsStaff()) {
            abort(403, 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}