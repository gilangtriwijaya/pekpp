<?php

namespace App\Jobs;

use App\Models\AnalyticsExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GenerateAnalyticsPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public AnalyticsExport $export;

    public function __construct(AnalyticsExport $export)
    {
        $this->export = $export;
    }

    public function handle()
    {
        // Placeholder: real implementation renders blade -> PDF using Browsershot
        $path = 'exports/analytics/'.now()->format('Y/m').'/'.$this->export->id.'.pdf';
        Storage::disk(config('analytics.storage_disk', 'local'))->put($path, "PDF_PLACEHOLDER");
        $this->export->update([
            'file_path' => $path,
            'file_size' => Storage::disk(config('analytics.storage_disk', 'local'))->size($path) ?? 0,
            'status' => 'ready',
            'processed_rows' => 0,
            'finished_at' => now(),
        ]);
    }
}
