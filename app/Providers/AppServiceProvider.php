<?php

namespace App\Providers;

use App\Models\lending_program_tbl;
use App\Observers\LendingProgramObserver;
use App\View\Composers\NavbarComposer;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        lending_program_tbl::observe(LendingProgramObserver::class);

        View::composer('components.navbar2', NavbarComposer::class);

        RateLimiter::for('login', function (Request $request) {
            $account = (string) ($request->input('login') ?? $request->input('email'));
            return Limit::perMinute(5)->by(Str::lower($account) . '|' . $request->ip());
        });

        RateLimiter::for('registration', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        RateLimiter::for('password-reset', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('otp-send', function (Request $request) {
            // Loosen while developing locally; tighten back up before deploying
            if (app()->environment('local')) {
                return Limit::perMinute(30)->by($request->ip());
            }
            return Limit::perMinutes(15, 5)->by($request->ip());
        });

        RateLimiter::for('otp-verify', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('2fa-challenge', function (Request $request) {
            // Keyed by the intended user combined with the client IP so a
            // single user/IP pair can attempt at most 5 codes per minute.
            $pendingUser = (int) $request->session()->get('2fa.pending_user_id', 0);

            return Limit::perMinute(5)->by('2fa:' . $pendingUser . '|' . $request->ip());
        });
    }
}
