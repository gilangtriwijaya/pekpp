<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\F01Pengisian;
use App\Models\F02Validasi;
use App\Models\F03Pengisian;
use Illuminate\Support\Facades\DB;

class ResetUppAnswersCommand extends Command
{
    protected $signature = 'upp:reset-answers {upp_id?} {--all}';
    protected $description = 'Reset semua jawaban (F01, F02, F03) dari UPP. Hanya hapus jawaban UPP, tidak ada data master yang dihapus.';

    public function handle()
    {
        $this->info('🔍 Checking data structure before reset...');
        $this->checkDataIntegrity();

        if ($this->option('all')) {
            return $this->resetAllUpps();
        }

        $uppId = $this->argument('upp_id');
        if (!$uppId) {
            $this->error('Usage: php artisan upp:reset-answers {upp_id} OR php artisan upp:reset-answers --all');
            return 1;
        }

        return $this->resetSingleUpp($uppId);
    }

    private function resetAllUpps()
    {
        $this->warn('⚠️  RESET SEMUA UPP - JAWABAN AKAN DIHAPUS SELAMANYA!');
        if (!$this->confirm('Lanjutkan reset semua UPP?')) {
            $this->info('Dibatalkan.');
            return 0;
        }

        try {
            DB::beginTransaction();

            // Disable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            // Show current stats
            $this->info("\n📊 Data sebelum reset:");
            $this->line('  F01 Pengisian: ' . F01Pengisian::withTrashed()->count());
            $this->line('  F02 Validasi: ' . F02Validasi::count());
            $this->line('  F03 Pengisian: ' . F03Pengisian::withTrashed()->count());

            // Hapus child tables dulu sebelum parent (foreign key constraint)
            // F01 children
            DB::table('f01_indikator_bukti')->delete();
            $this->info('✅ F01 Indikator Bukti dihapus');
            
            DB::table('f01_indikator_nilai')->delete();
            $this->info('✅ F01 Indikator Nilai dihapus');
            
            DB::table('f01_jawaban')->delete();
            $this->info('✅ F01 Jawaban dihapus');
            
            DB::table('f01_bukti_dukung')->delete();
            $this->info('✅ F01 Bukti Dukung dihapus');
            
            DB::table('f01_aspek_pengisian')->delete();
            $this->info('✅ F01 Aspek Pengisian dihapus');

            // F02 children
            DB::table('f02_catatan_indikator')->delete();
            $this->info('✅ F02 Catatan Indikator dihapus');
            
            DB::table('f02_indikator_validasi')->delete();
            $this->info('✅ F02 Indikator Validasi dihapus');

            // Hapus parent tables
            F01Pengisian::withTrashed()->forceDelete();
            $this->info('✅ F01 Pengisian dihapus');

            DB::table('f02_validasi')->delete();
            $this->info('✅ F02 Validasi dihapus');

            // F03 children
            DB::table('f03_jawaban')->delete();
            $this->info('✅ F03 Jawaban dihapus');
            
            DB::table('f03_response_demographics')->delete();
            $this->info('✅ F03 Response Demographic dihapus');

            // Hapus parent F03
            F03Pengisian::withTrashed()->forceDelete();
            $this->info('✅ F03 Pengisian dihapus');

            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            DB::commit();
            $this->info("\n✅ Reset semua UPP berhasil!");
            $this->verifyDataIntegrity();

            return 0;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Error: ' . $e->getMessage());
            return 1;
        }
    }

    private function resetSingleUpp($uppId)
    {
        try {
            DB::beginTransaction();

            // Validasi UPP ada
            $upp = DB::table('upps')->find($uppId);
            if (!$upp) {
                DB::rollBack();
                $this->error("❌ UPP ID {$uppId} tidak ditemukan");
                return 1;
            }

            $this->info("\n🔍 Menghitung data yang akan dihapus untuk UPP: {$upp->nama}");

            // Count sebelum reset (include soft deleted)
            $f01Count = F01Pengisian::withTrashed()->where('upp_id', $uppId)->count();
            $f02Count = DB::table('f02_validasi')
                ->whereIn('f01_pengisian_id', function($query) use ($uppId) {
                    $query->select('id')
                        ->from('f01_pengisian')
                        ->withTrashed()
                        ->where('upp_id', $uppId);
                })
                ->count();
            $f03Count = F03Pengisian::withTrashed()->where('upp_id', $uppId)->count();

            $this->line("  F01 Pengisian: {$f01Count}");
            $this->line("  F02 Validasi: {$f02Count}");
            $this->line("  F03 Pengisian: {$f03Count}");
            $this->line("  Total: " . ($f01Count + $f02Count + $f03Count));

            if ($f01Count == 0 && $f02Count == 0 && $f03Count == 0) {
                $this->info("\nℹ️  UPP ini tidak memiliki jawaban untuk direset.");
                DB::rollBack();
                return 0;
            }

            if (!$this->confirm("Hapus semua jawaban UPP '{$upp->nama}'?")) {
                $this->info('Dibatalkan.');
                DB::rollBack();
                return 0;
            }

            // Disable foreign key checks untuk reset
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            // Reset F01 untuk UPP ini - hapus children dulu
            if ($f01Count > 0) {
                $f01Ids = F01Pengisian::withTrashed()->where('upp_id', $uppId)->pluck('id');
                
                DB::table('f01_indikator_bukti')
                    ->whereIn('f01_indikator_nilai_id', function($query) use ($f01Ids) {
                        $query->select('id')
                            ->from('f01_indikator_nilai')
                            ->whereIn('f01_pengisian_id', $f01Ids);
                    })
                    ->delete();
                
                DB::table('f01_indikator_nilai')
                    ->whereIn('f01_pengisian_id', $f01Ids)
                    ->delete();
                
                DB::table('f01_jawaban')
                    ->whereIn('f01_pengisian_id', $f01Ids)
                    ->delete();
                
                DB::table('f01_bukti_dukung')
                    ->whereIn('f01_pengisian_id', $f01Ids)
                    ->delete();
                
                DB::table('f01_aspek_pengisian')
                    ->whereIn('f01_pengisian_id', $f01Ids)
                    ->delete();
                
                F01Pengisian::where('upp_id', $uppId)->forceDelete();
                $this->info("✅ {$f01Count} F01 Pengisian + children dihapus");
            }

            // Reset F02 untuk UPP ini
            if ($f02Count > 0) {
                $f01Ids = F01Pengisian::withTrashed()->where('upp_id', $uppId)->pluck('id');
                $f02Ids = DB::table('f02_validasi')
                    ->whereIn('f01_pengisian_id', $f01Ids)
                    ->pluck('id');
                
                DB::table('f02_catatan_indikator')
                    ->whereIn('f02_indikator_validasi_id', function($query) use ($f02Ids) {
                        $query->select('id')
                            ->from('f02_indikator_validasi')
                            ->whereIn('f02_validasi_id', $f02Ids);
                    })
                    ->delete();
                
                DB::table('f02_indikator_validasi')
                    ->whereIn('f02_validasi_id', $f02Ids)
                    ->delete();
                
                DB::table('f02_validasi')
                    ->whereIn('f01_pengisian_id', $f01Ids)
                    ->delete();
                
                $this->info("✅ {$f02Count} F02 Validasi + children dihapus");
            }

            // Reset F03 untuk UPP ini - hapus children dulu
            if ($f03Count > 0) {
                $f03Ids = F03Pengisian::withTrashed()->where('upp_id', $uppId)->pluck('id');
                
                DB::table('f03_jawaban')
                    ->whereIn('f03_pengisian_id', $f03Ids)
                    ->delete();
                
                DB::table('f03_response_demographics')
                    ->whereIn('f03_pengisian_id', $f03Ids)
                    ->delete();
                
                F03Pengisian::where('upp_id', $uppId)->forceDelete();
                $this->info("✅ {$f03Count} F03 Pengisian + children dihapus");
            }

            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            DB::commit();
            $this->info("\n✅ Reset berhasil untuk UPP: {$upp->nama}");
            $this->verifyDataIntegrity();

            return 0;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Error: ' . $e->getMessage());
            return 1;
        }
    }

    private function checkDataIntegrity()
    {
        $tables = [
            'upps' => 'UPP',
            'aspek' => 'Aspek',
            'indikator' => 'Indikator',
            'pertanyaan' => 'Pertanyaan',
            'f01_pengisian' => 'F01 Pengisian',
            'f02_validasi' => 'F02 Validasi',
            'f03_pengisian' => 'F03 Pengisian',
        ];

        foreach ($tables as $table => $name) {
            $exists = DB::getSchemaBuilder()->hasTable($table);
            $status = $exists ? '✓' : '✗';
            $this->line("  {$status} {$name} ({$table})");
        }
        $this->line('');
    }

    private function verifyDataIntegrity()
    {
        $this->info("\n✅ VERIFIKASI DATA MASTER:");
        $tables = [
            'upps' => 'Data UPP',
            'aspek' => 'Data Aspek',
            'indikator' => 'Data Indikator',
            'pertanyaan' => 'Data Pertanyaan',
            'periode' => 'Data Periode',
            'f03_aspek' => 'Template F03 Aspek',
            'f03_indikator' => 'Template F03 Indikator',
            'f03_token' => 'Token F03',
        ];

        foreach ($tables as $table => $name) {
            try {
                if (DB::getSchemaBuilder()->hasTable($table)) {
                    $count = DB::table($table)->count();
                    $this->line("  ✓ {$name}: {$count} record");
                } else {
                    $this->line("  - {$name}: table tidak ada");
                }
            } catch (\Exception $e) {
                $this->line("  - {$name}: tidak dapat diakses");
            }
        }

        $this->info("\n📌 Semua data master tetap aman dan siap digunakan kembali!");
    }
}
