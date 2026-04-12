<?php

namespace App\Console;

use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Console\Scheduling\Schedule;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\MirrorUsersFromSso::class,
        \App\Console\Commands\MirrorOpdsFromSso::class,
        \App\Console\Commands\SyncUppsFromOpd::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        // Run SSO mirror once per day at 03:10, avoid overlapping
        $schedule->command('sso:mirror-users --chunk=500')->dailyAt('03:10')->withoutOverlapping();
        // Mirror OPDs and OPD units hourly and also once daily at 03:15 for safety
        $schedule->command('sso:mirror-opds --per-page=200')->hourly()->withoutOverlapping();
        $schedule->command('sso:mirror-opds --per-page=200')->dailyAt('03:15')->withoutOverlapping();

        // Keep UPPs in sync with mirrored OPDs/OPD units — run hourly after mirror
        $schedule->command('upp:sync-from-opd')->hourly()->withoutOverlapping();
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
    }
}
