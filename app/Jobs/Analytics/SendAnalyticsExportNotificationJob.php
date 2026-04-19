<?php

namespace App\Jobs\Analytics;

use App\Models\AnalyticsExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendAnalyticsExportNotificationJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $exportId;

    public function __construct(int $exportId)
    {
        $this->exportId = $exportId;
    }

    public function handle()
    {
        // TODO: send email or in-app notification with signed url
    }
}
