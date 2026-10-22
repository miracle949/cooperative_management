<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectResignedMember
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if (
            $user
            && in_array(strtolower((string) $user->status), ['awaiting_release', 'resigned'], true)
            && !$request->routeIs('member.inactive', 'member.reactivate', 'logout', 'UserLogin', 'login')
        ) {
            return redirect()->route('member.inactive');
        }
        return $next($request);
    }
}