<?php

namespace App\Jobs;

use App\Models\AnalyticsExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;

class SendAnalyticsExportNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $exportId;

    public function __construct(int $exportId)
    {
        $this->exportId = $exportId;
    }

    public function handle()
    {
        $export = AnalyticsExport::find($this->exportId);
        if (!$export) {
            return;
        }

        $disk = config('analytics.storage_disk', 'local');
        $url = null;
        if ($export->file_path) {
            if ($disk === 's3') {
                $url = Storage::disk('s3')->temporaryUrl($export->file_path, now()->addHours(config('analytics.export_ttl_hours', 48)));
            } else {
                // local: provide signed route to download (controller checks auth)
                try {
                    $url = route('api.analytics.exports.download', ['id' => $export->id]);
                } catch (\Throwable $e) {
                    $url = null;
                }
            }
        }

        // For now, persist the download URL to the export record (helps UI)
        $export->update(['file_path' => $export->file_path, 'last_attempted_at' => now()]);

        // TODO: send email / in-app notification using Notification system
    }
}
