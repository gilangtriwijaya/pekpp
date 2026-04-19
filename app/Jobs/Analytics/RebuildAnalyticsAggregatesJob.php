<?php

namespace App\Jobs\Analytics;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RebuildAnalyticsAggregatesJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public function __construct()
    {
    }

    public function handle()
    {
        // TODO: implement full or partial rebuild logic
    }
}
