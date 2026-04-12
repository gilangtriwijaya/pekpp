<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequireSuperadmin
{
    /**
     * Handle an incoming request.
     * Allow only superadmin (system-level role)
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (! $user) {
            abort(403);
        }

        // Check if user is superadmin via role_sso (system-level role)
        if ($user->role_sso) {
            $role = strtolower(trim((string)$user->role_sso));
            if ($role === 'superadmin') {
                return $next($request);
            }
        }

        // Fallback to checking user_upp records (legacy UPP-level superadmin)
        $has = \Illuminate\Support\Facades\DB::table('user_upp')
            ->where('user_id', $user->id)
            ->where('peran', 'superadmin')
            ->where('aktif', 1)
            ->exists();

        if (! $has) {
            abort(403);
        }

        return $next($request);
    }
}
