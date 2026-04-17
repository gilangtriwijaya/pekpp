<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\Analytics\Metrics\AnalyticsMetrics;

class AnalyticsMetricsTest extends TestCase
{
    public function test_metrics_methods_callable()
    {
        $m = new AnalyticsMetrics();
        $m->incrementExportStarted('csv');
        $m->incrementExportSucceeded('csv');
        $m->incrementExportFailed('csv');
        $m->observeExportDuration('csv', 1.23);
        $m->observeRowsProcessed(100);

        $this->assertTrue(true); // no-op, ensures methods are callable
    }
}
