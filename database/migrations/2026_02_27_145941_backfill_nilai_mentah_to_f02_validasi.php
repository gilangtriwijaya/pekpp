<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Backfill nilai_mentah (raw score) for existing validasi records
     */
    public function up(): void
    {
        // Get all F02Validasi records that are 'selesai' but don't have nilai_mentah yet
        $validasis = DB::table('f02_validasi')
            ->where('status', 'selesai')
            ->whereNull('nilai_mentah')
            ->get();
        
        foreach ($validasis as $validasi) {
            // Calculate nilai_mentah from F02IndikatorValidasi
            $nilaiList = DB::table('f02_indikator_validasi')
                ->where('f02_validasi_id', $validasi->id)
                ->whereNotNull('nilai')
                ->pluck('nilai');
            
            if ($nilaiList->count() > 0) {
                $nilaiMentah = $nilaiList->sum() / $nilaiList->count();
                
                // Update F02Validasi dengan nilai_mentah
                DB::table('f02_validasi')
                    ->where('id', $validasi->id)
                    ->update(['nilai_mentah' => round($nilaiMentah, 2)]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reset nilai_mentah to NULL for records we just filled
        DB::table('f02_validasi')->update(['nilai_mentah' => null]);
    }
};
