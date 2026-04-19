<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\Analytics\ScopeContext;

class EnsureScopeContext
{
    public function handle(Request $request, Closure $next)
    {
        // If scope_context provided in body, normalize it to a ScopeContext instance and attach to request
        try {
            $sc = ScopeContext::fromRequest($request);
            $request->attributes->set('scope_context', $sc->toArray());
        } catch (\Throwable $e) {
            // fallthrough - request may still be valid but without scope context
        }

        return $next($request);
    }
}
