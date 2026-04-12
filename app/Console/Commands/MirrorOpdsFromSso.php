<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Opd;
use App\Models\OpdUnit;
use App\Models\SsoSyncLog;
use App\Services\SsoClient;

class MirrorOpdsFromSso extends Command
{
    protected $signature = 'sso:mirror-opds {--since= : only mirror updated since this datetime} {--per-page=200 : per page size}';

    protected $description = 'Mirror OPDs and OPD units from SSO into local tables';

    public function handle(): int
    {
        $perPage = max(10, (int)$this->option('per-page'));
        $since = $this->option('since');

        $totalOpds = 0; $insertedOpds = 0; $updatedOpds = 0;
        $totalUnits = 0; $insertedUnits = 0; $updatedUnits = 0;

        $sso = new SsoClient();

        $log = null;
        try {
            try { $log = SsoSyncLog::create(['command' => 'sso:mirror-opds', 'started_at' => now(), 'status' => 'running']); } catch (\Throwable $_) {}

            // 1) OPDs
            $page = 1;
            while (true) {
                $resp = $sso->fetchOpdsPage($page, $perPage, $since);
                $rows = $resp['data'] ?? [];
                if (empty($rows)) break;

                foreach ($rows as $r) {
                    $totalOpds++;
                    $ssoId = (int)($r['id'] ?? 0);
                    if (!$ssoId) continue;

                    $name = $r['name'] ?? ($r['nama'] ?? null);
                    $payload = [
                        'sso_id' => $ssoId,
                        'nama' => $name,
                        'slug' => $name ? Str::slug($name) : null,
                    ];

                    $exists = Opd::where('sso_id', $ssoId)->exists();
                    Opd::updateOrCreate(['sso_id' => $ssoId], $payload);
                    if ($exists) $updatedOpds++; else $insertedOpds++;
                }

                $page++;
                if (!empty($resp['meta']['last_page']) && $page > (int)$resp['meta']['last_page']) break;
            }

            // Refresh OPD map for units mapping
            $opdMap = DB::table('opds')->pluck('id', 'sso_id')->all();

            // 2) OPD units
            $page = 1;
            while (true) {
                $resp = $sso->fetchOpdUnitsPage($page, $perPage, $since);
                $rows = $resp['data'] ?? [];
                if (empty($rows)) break;

                foreach ($rows as $r) {
                    $totalUnits++;
                    $ssoId = (int)($r['id'] ?? 0);
                    if (!$ssoId) continue;

                    $name = $r['name'] ?? ($r['nama'] ?? null);
                    $ssoOpdId = (int)($r['opd_id'] ?? ($r['opdId'] ?? 0));
                    $localOpdId = $opdMap[$ssoOpdId] ?? null;

                    $payload = [
                        'sso_id' => $ssoId,
                        'opd_id' => $localOpdId,
                        'nama' => $name,
                    ];

                    $exists = OpdUnit::where('sso_id', $ssoId)->exists();
                    OpdUnit::updateOrCreate(['sso_id' => $ssoId], $payload);
                    if ($exists) $updatedUnits++; else $insertedUnits++;
                }

                $page++;
                if (!empty($resp['meta']['last_page']) && $page > (int)$resp['meta']['last_page']) break;
            }

            $this->info("OPDs: total={$totalOpds}, inserted={$insertedOpds}, updated={$updatedOpds}");
            $this->info("OPD Units: total={$totalUnits}, inserted={$insertedUnits}, updated={$updatedUnits}");

            if ($log) { try { $log->update(['finished_at'=>now(), 'status'=>'success', 'message'=>"opds:ins={$insertedOpds},upd={$updatedOpds}; units:ins={$insertedUnits},upd={$updatedUnits}"]); } catch (\Throwable $_) {} }

            return self::SUCCESS;
        } catch (\Throwable $e) {
            if ($log) { try { $log->update(['finished_at'=>now(), 'status'=>'failed', 'message'=>$e->getMessage()]); } catch (\Throwable $_) {} }
            $this->error('GAGAL: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
