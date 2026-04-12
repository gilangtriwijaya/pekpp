<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\UserUnitRoleLegacy;
use App\Models\UserUpp;
use App\Models\User;
use App\Models\Upp;

use Illuminate\Support\Facades\Storage;

class PopulateUserUppFromLegacy extends Command
{
    protected $signature = 'user_upp:populate-from-legacy {--dry : do not write, only report} {--chunk=200 : chunk size}';

    protected $description = 'Populate `user_upp` from legacy `user_unit_roles_legacy` mapping users -> upps with controlled role mapping.';

    public function handle(): int
    {
        $dry = $this->option('dry');
        $chunk = (int)$this->option('chunk');

        $total = 0; $created = 0; $updated = 0; $skipped = 0;

        $this->info('Starting populate user_upp from legacy');

        // prepare failure log
        $logPath = storage_path('app/user_upp_migration_failures.csv');
        if (! file_exists($logPath)) {
            file_put_contents($logPath, "legacy_id,user_identifier,opd_unit_id,role,reason\n");
        }

        $query = UserUnitRoleLegacy::query();
        $query->orderBy('id');

        $query->chunk($chunk, function ($rows) use (&$total, &$created, &$updated, &$skipped, $dry, $logPath) {
            foreach ($rows as $r) {
                $total++;

                // attempt to resolve user local id
                $userLocalId = $this->resolveUserId($r->user_id);
                if (! $userLocalId) {
                    $this->warn("skip #{$r->id}: user not found ({$r->user_id})");
                    $this->logFailure($logPath, $r, 'user not found');
                    $skipped++; continue;
                }

                // attempt to resolve opd_unit -> find upp by unit_opd_id_sso
                $uppId = $this->resolveUppId($r->opd_unit_id);
                if (! $uppId) {
                    $this->warn("skip #{$r->id}: upp not found for opd_unit_id={$r->opd_unit_id}");
                    $this->logFailure($logPath, $r, 'upp not found');
                    $skipped++; continue;
                }

                // map role
                $role = $this->mapRole($r->role);
                if (! $role) {
                    $this->warn("skip #{$r->id}: role unknown ({$r->role})");
                    $this->logFailure($logPath, $r, 'role unknown');
                    $skipped++; continue;
                }

                $attrs = ['user_id' => $userLocalId, 'upp_id' => $uppId, 'peran' => $role];
                $values = ['aktif' => 1, 'ditetapkan_oleh' => null, 'ditetapkan_pada' => now()];

                if ($dry) {
                    $this->line("DRY: would upsert " . json_encode($attrs));
                    $created++; continue;
                }

                $row = UserUpp::updateOrCreate($attrs, $values);
                if (property_exists($row, 'wasRecentlyCreated') ? $row->wasRecentlyCreated : false) $created++; else $updated++;
            }
        });

        $this->info("Done: total={$total}, created={$created}, updated={$updated}, skipped={$skipped}");
        return self::SUCCESS;
    }

    protected function resolveUserId($legacyUserId)
    {
        // try local id
        if (is_numeric($legacyUserId)) {
            $u = User::find((int)$legacyUserId);
            if ($u) return $u->id;
        }

        // try sso_user_id
        $u = User::where('sso_user_id', $legacyUserId)->first();
        if ($u) return $u->id;

        // try email/username
        $u = User::where('email', $legacyUserId)->orWhere('username', $legacyUserId)->first();
        if ($u) return $u->id;

        return null;
    }

    protected function resolveUppId($opdUnitId)
    {
        if (! $opdUnitId) return null;

        // Rule A: try match upps.unit_opd_id_sso == opdUnitId
        $upp = Upp::where('unit_opd_id_sso', $opdUnitId)->first();
        if ($upp) return $upp->id;

        // Rule B: try match upps.opd_id_sso == opdUnitId
        $upp = Upp::where('opd_id_sso', $opdUnitId)->first();
        if ($upp) return $upp->id;

        // not found
        return null;
    }

    protected function logFailure(string $logPath, $legacyRow, string $reason)
    {
        $line = sprintf("%s,%s,%s,%s,%s\n",
            $legacyRow->id,
            str_replace(',', ' ', (string)$legacyRow->user_id),
            str_replace(',', ' ', (string)$legacyRow->opd_unit_id),
            str_replace(',', ' ', (string)$legacyRow->role),
            $reason
        );
        file_put_contents($logPath, $line, FILE_APPEND | LOCK_EX);
    }

    protected function mapRole($raw)
    {
        $r = strtolower(trim((string)$raw));
        if ($r === '') return null;

        if (str_contains($r, 'super')) return 'superadmin';
        if (str_contains($r, 'organis')) return 'admin_organisasi';
        if ($r === 'admin_opd' || $r === 'org_admin') return 'admin_upp';
        if (str_starts_with($r, 'admin')) return 'admin_upp';
        if (str_contains($r, 'verifik')) return 'verifikator';

        return null;
    }
}
