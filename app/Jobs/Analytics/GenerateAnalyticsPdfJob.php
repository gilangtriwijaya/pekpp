<?php

namespace App\Jobs\Analytics;

use App\Models\AnalyticsExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateAnalyticsPdfJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public $exportId;
    public $tries = 5;

    public function __construct(int $exportId)
    {
        $this->exportId = $exportId;
    }

    public function handle()
    {
        // TODO: render blade to HTML, convert to PDF via snappy/dompdf, update AnalyticsExport
    }
}
