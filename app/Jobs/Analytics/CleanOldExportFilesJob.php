<?php

namespace App\Jobs\Analytics;

use App\Models\AnalyticsExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class CleanOldExportFilesJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        $retention = config('analytics.export_retention_days', 30);
        $cutoff = Carbon::now()->subDays($retention);
        $exports = AnalyticsExport::where('finished_at', '<', $cutoff)->whereNotNull('file_path')->get();
        foreach ($exports as $exp) {
            Storage::disk(config('analytics.storage_disk'))->delete($exp->file_path);
            $exp->file_path = null;
            $exp->status = 'failed';
            $exp->save();
        }
    }
}
