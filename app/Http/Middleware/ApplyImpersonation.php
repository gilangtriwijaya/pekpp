<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;

class ApplyImpersonation
{
    /**
     * Handle an incoming request.
     * 
     * If user is being impersonated, replace the authenticated user with the impersonated user
     * for authorization checks and data loading, but track the original superadmin who initiated it.
     */
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && auth()->user()->hasGlobalRole('superadmin')) {
            $impersonatingUserId = session()->get('impersonating_user_id');

            if ($impersonatingUserId) {
                $impersonatedUser = User::find($impersonatingUserId);

                if ($impersonatedUser) {
                    // Replace the authenticated user with the impersonated user
                    auth()->setUser($impersonatedUser);

                    // Add impersonation info to request for logging/tracking
                    $request->attributes->set('impersonating', true);
                }
            }
        }

        return $next($request);
    }
}
