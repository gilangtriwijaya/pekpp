<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // First, fill null catatan with empty string
        \DB::table('f02_indikator_validasi')
            ->whereNull('catatan')
            ->update(['catatan' => '']);
        
        Schema::table('f02_indikator_validasi', function (Blueprint $table) {
            // Drop old skor column if exists
            if (Schema::hasColumn('f02_indikator_validasi', 'skor')) {
                $table->dropColumn('skor');
            }
            
            // Add new nilai column (1-5 scale)
            if (!Schema::hasColumn('f02_indikator_validasi', 'nilai')) {
                $table->unsignedTinyInteger('nilai')->nullable()->comment('Nilai validasi 1-5');
            }
            
            // Make catatan not nullable
            if (Schema::hasColumn('f02_indikator_validasi', 'catatan')) {
                $table->text('catatan')->nullable(false)->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('f02_indikator_validasi', function (Blueprint $table) {
            // Revert back to skor
            if (!Schema::hasColumn('f02_indikator_validasi', 'skor')) {
                $table->decimal('skor', 8, 2)->nullable();
            }
            
            // Drop nilai
            if (Schema::hasColumn('f02_indikator_validasi', 'nilai')) {
                $table->dropColumn('nilai');
            }
            
            // Make catatan nullable again
            if (Schema::hasColumn('f02_indikator_validasi', 'catatan')) {
                $table->text('catatan')->nullable()->change();
            }
        });
    }
};
