<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncUserUpp extends Command
{
    protected $signature = 'user_upp:sync {--from=both : source to sync (legacy|csv|both)} {--file= : path to CSV when using csv source} {--dry : dry-run}';

    protected $description = 'Safely sync/populate `user_upp` from legacy or CSV sources after verifying prerequisites.';

    public function handle(): int
    {
        $from = strtolower((string)$this->option('from'));
        $file = $this->option('file');
        $dry = $this->option('dry');

        $this->info("user_upp:sync starting (from={$from})");

        $userCount = DB::table('users')->count();
        $uppCount = DB::table('upps')->count();

        if ($userCount <= 0) {
            $this->error('Aborted: no users found in `users` table. Ensure SSO mirror ran.');
            return self::FAILURE;
        }

        if ($uppCount <= 0) {
            $this->error('Aborted: no UPPs found in `upps` table. Ensure UPP sync ran.');
            return self::FAILURE;
        }

        // Decide what to run
        $doLegacy = $from === 'legacy' || $from === 'both' || $from === '';
        $doCsv = $from === 'csv' || $from === 'both';

        if ($doLegacy) {
            $this->line('Running legacy migration step (user_unit_roles) → user_upp');
            $this->call('user_upp:populate-from-legacy', ['--dry' => $dry]);
        }

        if ($doCsv) {
            if (! $file) {
                $this->error('CSV source requested but --file not provided.');
                return self::FAILURE;
            }
            $this->line("Importing CSV: {$file}");
            $this->call('user_upp:import-csv', ['file' => $file, '--dry' => $dry]);
        }

        $this->info('user_upp:sync completed');
        return self::SUCCESS;
    }
}
