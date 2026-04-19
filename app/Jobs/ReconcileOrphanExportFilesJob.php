<?php

namespace App\Jobs;

use App\Models\AnalyticsExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ReconcileOrphanExportFilesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        // Find files on disk not referenced by DB and delete them (reconcile)
        // Implementation left as an exercise for infra specifics (S3 vs local)
    }
}
