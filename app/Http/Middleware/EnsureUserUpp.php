<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureUserUpp
{
    /**
     * Ensure the authenticated user's `userUpps` relation is loaded and available.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if ($user) {
            // eager load assigned UPPs + related UPP
            $user->loadMissing('userUpps.upp');
        }

        return $next($request);
    }
}
