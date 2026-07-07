<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Add session expiry check middleware to web routes
        $middleware->web([
            \App\Http\Middleware\CheckSessionExpiry::class,
            \App\Http\Middleware\ApplyImpersonation::class,
        ]);
        // Alias for publik API key middleware
        $middleware->alias([
            'api.publik.key' => \App\Http\Middleware\ValidasiApiKeyPublik::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('statpub:rebuild')
            ->hourly()
            ->appendOutputTo(storage_path('logs/statpub-rebuild.log'))
            ->withoutOverlapping();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle unauthenticated requests - show session expired page if coming from authenticated session
        $exceptions->render(function (AuthenticationException $e, $request) {
            return redirect()->route('session-expired');
        });    })->create();
