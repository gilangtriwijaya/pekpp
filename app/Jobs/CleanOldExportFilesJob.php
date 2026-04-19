<?php

namespace App\Jobs;

use App\Models\AnalyticsExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class CleanOldExportFilesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        $retentionDays = config('analytics.export_retention_days', 30);
        $cutoff = now()->subDays($retentionDays);

        AnalyticsExport::whereNotNull('file_path')
            ->where('finished_at', '<', $cutoff)
            ->chunkById(100, function ($rows) {
                foreach ($rows as $r) {
                    if ($r->file_path && Storage::disk(config('analytics.storage_disk', 'local'))->exists($r->file_path)) {
                        Storage::disk(config('analytics.storage_disk', 'local'))->delete($r->file_path);
                    }
                    $r->update(['file_path' => null, 'status' => 'failed']);
                }
            });
    }
}
