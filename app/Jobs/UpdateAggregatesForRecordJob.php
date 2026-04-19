<?php

namespace App\Jobs;

use Illuminate\Support\Facades\DB;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\Analytics\AnalyticsAggregationService;

class UpdateAggregatesForRecordJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $f02Id;
    public array|null $scopeContext;

    public $tries = 3;

    public function __construct(int $f02Id, ?array $scopeContext = null)
    {
        $this->f02Id = $f02Id;
        $this->scopeContext = $scopeContext;
    }

    public function handle(AnalyticsAggregationService $service)
    {
        // TODO: implement incremental update logic for single F02 record
        // For now: re-run full rebuild for the period of this record (safe but heavier)
        $periodeId = DB::table('f02_validasi as v')->join('f01_pengisian as f', 'v.f01_pengisian_id', 'f.id')
            ->where('v.id', $this->f02Id)
            ->value('f.periode_id');

        if ($periodeId) {
            $service->rebuildPeriod($periodeId, $this->scopeContext);
        }
    }
}
