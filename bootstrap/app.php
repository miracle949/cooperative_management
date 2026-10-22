<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsStaff::class,
            'member.active' => \App\Http\Middleware\EnsureMemberActive::class,
            'resigned.redirect' => \App\Http\Middleware\RedirectResignedMember::class,
        ]);

        $middleware->prepend(\App\Http\Middleware\ForceHttps::class);
        $middleware->prepend(\App\Http\Middleware\SetSecurityHeaders::class);

        $middleware->web(append: [
            \App\Http\Middleware\RequireTwoFactor::class,
            \App\Http\Middleware\EnforceSingleSession::class,   // ← add this line
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();