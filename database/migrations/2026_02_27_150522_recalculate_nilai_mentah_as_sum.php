<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Recalculate nilai_mentah as SUM instead of AVERAGE
     */
    public function up(): void
    {
        // Get all F02Validasi records that are 'selesai'
        $validasis = DB::table('f02_validasi')
            ->where('status', 'selesai')
            ->get();
        
        foreach ($validasis as $validasi) {
            // Calculate nilai_mentah as SUM (not average)
            $totalSkor = DB::table('f02_indikator_validasi')
                ->where('f02_validasi_id', $validasi->id)
                ->whereNotNull('nilai')
                ->sum('nilai');
            
            if ($totalSkor > 0) {
                // Update F02Validasi dengan nilai_mentah = sum
                DB::table('f02_validasi')
                    ->where('id', $validasi->id)
                    ->update(['nilai_mentah' => round($totalSkor, 2)]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reset nilai_mentah to NULL
        DB::table('f02_validasi')->update(['nilai_mentah' => null]);
    }
};
