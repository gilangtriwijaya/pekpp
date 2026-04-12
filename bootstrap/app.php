<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Add session expiry check middleware to web routes
        $middleware->web([
            \App\Http\Middleware\CheckSessionExpiry::class,
            \App\Http\Middleware\ApplyImpersonation::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle unauthenticated requests - show session expired page if coming from authenticated session
        $exceptions->render(function (AuthenticationException $e, $request) {
            return redirect()->route('session-expired');
        });    })->create();