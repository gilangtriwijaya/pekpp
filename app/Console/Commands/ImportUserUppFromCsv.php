<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;
use League\Csv\Statement;
use App\Models\User;
use App\Models\Upp;
use App\Models\UserUpp;

class ImportUserUppFromCsv extends Command
{
    protected $signature = 'user_upp:import-csv {file : path to CSV} {--dry : dry-run} {--hasHeader=1 : CSV has header row (1/0)}';

    protected $description = 'Import user_upp from CSV. CSV columns: user_key, upp_key, role. user_key = id|sso_user_id|email; upp_key = id|kode|unit_opd_id_sso';

    public function handle(): int
    {
        $path = $this->argument('file');
        $dry = (bool)$this->option('dry');
        $hasHeader = (int)$this->option('hasHeader') === 1;

        if (! file_exists($path)) {
            $this->error('File not found: ' . $path);
            return self::FAILURE;
        }

        try {
            $csv = Reader::createFromPath($path, 'r');
            if ($hasHeader) $csv->setHeaderOffset(0);
        } catch (\Throwable $e) {
            $this->error('Failed to open CSV: ' . $e->getMessage());
            return self::FAILURE;
        }

        $stmt = (new Statement());
        $records = $stmt->process($csv);

        $rowNo = 0; $matched=0;$created=0;$updated=0;$skipped=0;

        foreach ($records as $rec) {
            $rowNo++;
            // support both header-based and positional
            if ($hasHeader) {
                $userKey = $rec['user_key'] ?? null;
                $uppKey = $rec['upp_key'] ?? null;
                $role = $rec['role'] ?? null;
            } else {
                $userKey = $rec[0] ?? null;
                $uppKey = $rec[1] ?? null;
                $role = $rec[2] ?? null;
            }

            if (! $userKey || ! $uppKey || ! $role) {
                $this->warn("row {$rowNo} missing columns, skip"); $skipped++; continue;
            }

            $userId = $this->resolveUser($userKey);
            if (! $userId) { $this->warn("row {$rowNo}: user not found ({$userKey})"); $skipped++; continue; }

            $uppId = $this->resolveUpp($uppKey);
            if (! $uppId) { $this->warn("row {$rowNo}: upp not found ({$uppKey})"); $skipped++; continue; }

            $mapped = $this->mapRole($role);
            if (! $mapped) { $this->warn("row {$rowNo}: role unknown ({$role})"); $skipped++; continue; }

            $matched++;
            if ($dry) { $this->line("DRY row {$rowNo}: user_id={$userId}, upp_id={$uppId}, peran={$mapped}"); continue; }

            $row = UserUpp::updateOrCreate(['user_id'=>$userId,'upp_id'=>$uppId,'peran'=>$mapped], ['aktif'=>1,'ditetapkan_pada'=>now(), 'hasil_fallback'=>0]);
            if ($row->wasRecentlyCreated ?? false) $created++; else $updated++;
        }

        $this->info("Done: total={$rowNo}, matched={$matched}, created={$created}, updated={$updated}, skipped={$skipped}");
        return self::SUCCESS;
    }

    protected function resolveUser(string $key)
    {
        // numeric -> try id, then sso_user_id
        if (is_numeric($key)) {
            $u = User::find((int)$key);
            if ($u) return $u->id;
            $u = User::where('sso_user_id', (int)$key)->first();
            if ($u) return $u->id;
        }

        // email
        $u = User::where('email', $key)->first(); if ($u) return $u->id;

        // fallback username
        $u = User::where('username', $key)->first(); if ($u) return $u->id;

        return null;
    }

    protected function resolveUpp(string $key)
    {
        if (is_numeric($key)) {
            $u = Upp::find((int)$key); if ($u) return $u->id;
        }

        // try kode
        $u = Upp::where('kode', $key)->first(); if ($u) return $u->id;

        // try unit_opd_id_sso
        $u = Upp::where('unit_opd_id_sso', $key)->first(); if ($u) return $u->id;

        return null;
    }

    protected function mapRole(string $r)
    {
        $s = strtolower(trim($r));
        if (str_contains($s,'super')) return 'superadmin';
        if (str_contains($s,'organis') || $s === 'admin_opd') return 'admin_organisasi';
        if (str_starts_with($s,'admin')) return 'admin_upp';
        if (str_contains($s,'verifik')) return 'verifikator';
        return null;
    }
}
