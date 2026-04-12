<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ActivityLog;

class LogActivity
{
    /**
     * Handle an incoming request and record activity.
     */
    public function handle(Request $request, Closure $next)
    {
        // Let the request run first so controllers can update session/state before we capture final details.
        $response = $next($request);

        // Do not log asset requests or CLI/internal requests
        if ($request->is('favicon.ico') || $request->is('css/*') || $request->is('js/*') || $request->is('images/*') || $request->is('vendor/*')) {
            return $response;
        }

        // Exclude activity-logs admin UI routes to avoid noise
        if ($request->is('activity-logs') || $request->is('activity-logs/*')) {
            return $response;
        }

        // Avoid logging very small probes/HEAD
        if ($request->method() === 'HEAD') return $response;

        try {
            $action = $request->method() . ' ' . $request->route()?->getName() ?? 'request';
            $params = $request->except(['_token','password','password_confirmation']);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => $action,
                'route' => $request->route()?->getName(),
                'method' => $request->method(),
                'path' => $request->path(),
                'params' => $params ? $params : null,
                'ip' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
            ]);
        } catch (\Throwable $e) {
            // best-effort logging; never break the request flow
            logger()->warning('ActivityLog failed: '.$e->getMessage());
        }

        return $response;
    }
}
