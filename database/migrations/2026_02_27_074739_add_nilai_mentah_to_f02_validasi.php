<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add nilai_mentah (raw score) to track unweighted average of all indikator scores
     */
    public function up(): void
    {
        Schema::table('f02_validasi', function (Blueprint $table) {
            $table->decimal('nilai_mentah', 5, 2)->nullable()->after('total_nilai')
                ->comment('Raw score: average of all indikator scores without bobot weighting');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('f02_validasi', function (Blueprint $table) {
            $table->dropColumn('nilai_mentah');
        });
    }
};
