<?php

namespace App\Jobs;

use App\Models\AnalyticsExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendAnalyticsExportNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected AnalyticsExport $export;

    public function __construct(AnalyticsExport $export)
    {
        $this->export = $export;
    }

    public function handle()
    {
        // TODO: send email/in-app notification with signed URL
    }
}
