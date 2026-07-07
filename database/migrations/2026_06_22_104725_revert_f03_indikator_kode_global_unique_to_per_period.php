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
        Schema::table('f03_indikator', function (Blueprint $table) {
            $table->dropUnique('uq_f03_indikator_kode');
            $table->unique(['periode_id', 'f03_aspek_id', 'kode'], 'uq_f03_indikator_periode_aspek_kode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('per_period', function (Blueprint $table) {
            //
        });
    }
};
