<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CheckSessionExpiry
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Skip check for login/logout routes
        if ($request->routeIs('sso.login', 'sso.callback', 'sso.logout', 'sso.back', 'login', 'session-expired')) {
            return $next($request);
        }

        // Only check if user is authenticated
        if (Auth::check()) {
            // Check if session idle timeout exceeded
            $lastActivity = session('_last_activity', now()->timestamp);
            $sessionLifetime = config('session.lifetime') * 60; // Convert minutes to seconds

            $currentTime = now()->timestamp;
            $timeSinceLastActivity = $currentTime - $lastActivity;

            // If idle time exceeds session lifetime, logout
            if ($timeSinceLastActivity > $sessionLifetime) {
                Log::info('Session expired due to idle timeout', [
                    'user_id' => Auth::id(),
                    'idle_seconds' => $timeSinceLastActivity,
                    'max_seconds' => $sessionLifetime,
                ]);

                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('session-expired');
            }

            // Update last activity timestamp
            session(['_last_activity' => $currentTime]);
        }

        return $next($request);
    }
}
