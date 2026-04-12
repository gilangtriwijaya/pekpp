<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, fix existing duplicates by renaming them with unique numbers
        $indikators = \App\Models\F03Indikator::all()->sortBy('id');
        $lastNumber = 0;
        $kodesUsed = [];
        
        foreach ($indikators as $ind) {
            // Extract the numeric part from existing kode
            if (preg_match('/^FI(\d+)$/', $ind->kode, $matches)) {
                $num = intval($matches[1]);
                $lastNumber = max($lastNumber, $num);
            }
        }
        
        // Renumber all to ensure uniqueness
        foreach ($indikators as $ind) {
            if (!isset($kodesUsed[$ind->kode])) {
                $kodesUsed[$ind->kode] = 0;
            }
            $kodesUsed[$ind->kode]++;
            
            // If this kode has duplicates, renumber it
            $allWithSameKode = \App\Models\F03Indikator::where('kode', $ind->kode)->get();
            if ($allWithSameKode->count() > 1) {
                $lastNumber++;
                $ind->kode = 'FI' . str_pad($lastNumber, 3, '0', STR_PAD_LEFT);
                $ind->save();
            }
        }
        
        // Drop the old unique constraint
        Schema::table('f03_indikator', function (Blueprint $table) {
            $table->dropUnique('uq_f03_indikator_periode_aspek_kode');
        });
        
        // Add new global unique constraint on kode
        Schema::table('f03_indikator', function (Blueprint $table) {
            $table->unique('kode', 'uq_f03_indikator_kode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to per-aspek unique constraint
        Schema::table('f03_indikator', function (Blueprint $table) {
            $table->dropUnique('uq_f03_indikator_kode');
        });
        
        Schema::table('f03_indikator', function (Blueprint $table) {
            $table->unique(['periode_id', 'f03_aspek_id', 'kode'], 'uq_f03_indikator_periode_aspek_kode');
        });
    }
};
