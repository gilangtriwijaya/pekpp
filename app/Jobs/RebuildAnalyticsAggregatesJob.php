<?php

namespace App\Jobs;

use App\Services\Analytics\AnalyticsAggregationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RebuildAnalyticsAggregatesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $periodeId;
    public array|null $scopeContext;

    public $tries = 3;

    public function __construct(int $periodeId, ?array $scopeContext = null)
    {
        $this->periodeId = $periodeId;
        $this->scopeContext = $scopeContext;
    }

    public function handle(AnalyticsAggregationService $service)
    {
        // Rebuild aggregates for period
        $count = $service->rebuildPeriod($this->periodeId, $this->scopeContext);
        // optional: log or emit metric
        logger()->info('RebuildAnalyticsAggregatesJob completed', ['periode' => $this->periodeId, 'rows' => $count]);
    }
}
