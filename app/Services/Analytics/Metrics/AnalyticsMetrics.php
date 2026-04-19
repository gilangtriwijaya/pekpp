<?php

namespace App\Services\Analytics\Metrics;

use Illuminate\Support\Facades\Log;

/**
 * Lightweight metrics emitter abstraction for analytics.
 * Replace implementation with Prometheus client or StatsD exporter in production.
 */
class AnalyticsMetrics
{
    public function incrementExportStarted(string $type = 'csv') : void
    {
        Log::info('analytics.metric.export.started', ['type' => $type]);
    }

    public function incrementExportSucceeded(string $type = 'csv') : void
    {
        Log::info('analytics.metric.export.succeeded', ['type' => $type]);
    }

    public function incrementExportFailed(string $type = 'csv') : void
    {
        Log::info('analytics.metric.export.failed', ['type' => $type]);
    }

    public function observeExportDuration(string $type, float $seconds) : void
    {
        Log::info('analytics.metric.export.duration', ['type' => $type, 'seconds' => $seconds]);
    }

    public function observeRowsProcessed(int $count) : void
    {
        Log::info('analytics.metric.export.rows', ['rows' => $count]);
    }
}
