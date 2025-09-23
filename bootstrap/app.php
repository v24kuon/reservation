<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            '/stripe/webhook',
        ]);

        // When accessing guest-only routes (e.g., /login) while authenticated,
        // redirect users based on their role: privileged → dashboard, others → home.
        $middleware->redirectUsersTo(function () {
            $user = auth()->user();
            if ($user && method_exists($user, 'hasPrivilegedRole') && $user->hasPrivilegedRole()) {
                return route('dashboard', absolute: false);
            }
            return route('home', absolute: false);
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
