<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\User as UserModel;
use App\Models\Role as RoleModel;
use App\Models\UserUnitRoleLegacy as UserUnitRoleModel;
use App\Models\SsoSyncLog;
use App\Services\SsoClient;
use App\Services\UserSyncService;

class MirrorUsersFromSso extends Command
{
    protected $signature = 'sso:mirror-users {--since= : only mirror users updated since this datetime} {--chunk=500 : chunk size}';

    protected $description = 'Mirror users from SSO DB into local users (by sso_user_id)';

    public function handle(): int
    {
        $chunk = (int)$this->option('chunk');
        $since = $this->option('since');

        $total = 0; $inserted = 0; $updated = 0; $skipped = 0;

        $opdMap = DB::table('opds')->pluck('id', 'sso_id')->all();

        $sso = new SsoClient();
        $syncService = new UserSyncService();

        // create a log entry and run paged fetch so we always store finish timestamp
        $log = null;
        try {
            try { $log = SsoSyncLog::create(['command' => 'sso:mirror-users', 'started_at' => now(), 'status' => 'running']); } catch (\Throwable $_) {}

            $page = 1;
            $perPage = max(10, $chunk);
            while (true) {
                $resp = $sso->fetchUsersPage($page, $perPage, $since);
                $rows = $resp['data'] ?? [];
                if (empty($rows)) break;

                foreach ($rows as $r) {
                    $total++;
                    $ssoId = (int)($r['id'] ?? 0);
                    if (!$ssoId) { $skipped++; continue; }

                    $mappedOpd = $opdMap[(int)($r['opd_id'] ?? 0)] ?? null;
                    $mappedUnit = null;
                    if (! empty($r['opd_unit_id'])) {
                        $mappedUnit = DB::table('opd_units')->where('sso_id', (int)$r['opd_unit_id'])->value('id');
                    }

                    $exists = DB::table('users')->where('sso_user_id', $ssoId)->exists();
                    try {
                        $syncService->syncFromPayload($r, ['opd_id' => $mappedOpd, 'opd_unit_id' => $mappedUnit]);
                        if ($exists) $updated++; else $inserted++;
                    } catch (\Throwable $e) {
                        $skipped++;
                        $this->error('Skipped user id=' . ($r['id'] ?? 'n/a') . ' reason: ' . $e->getMessage());
                    }
                }

                $page++;
                // stop if pagination meta indicates last page
                if (!empty($resp['meta']['last_page']) && $page > (int)$resp['meta']['last_page']) break;
            }

            $this->info("OK: total={$total}, inserted={$inserted}, updated={$updated}, skipped={$skipped}");
            if ($log) { try { $log->update(['finished_at'=>now(), 'status'=>'success', 'message'=>"inserted={$inserted}, updated={$updated}, skipped={$skipped}"]); } catch (\Throwable $_) {} }
            return self::SUCCESS;
        } catch (\Throwable $e) {
            try { if ($log) $log->update(['finished_at'=>now(), 'status'=>'failed', 'message'=>$e->getMessage()]); } catch (\Throwable $_) {}
            $this->error('GAGAL: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
