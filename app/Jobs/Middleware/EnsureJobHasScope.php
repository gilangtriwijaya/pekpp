<?php

namespace App\Jobs\Middleware;

use Closure;

class EnsureJobHasScope
{
    /**
     * Handle job middleware
     */
    public function handle($job, $next)
    {
        // Best-effort: if job has exportId or scopeContext, ensure it's present
        if (property_exists($job, 'exportId') && empty($job->exportId)) {
            throw new \RuntimeException('job_missing_export_id');
        }

        // Allow job to proceed; further validation can be added in each job
        return $next($job);
    }
}
