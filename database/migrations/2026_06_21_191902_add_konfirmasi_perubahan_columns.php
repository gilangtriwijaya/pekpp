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
        Schema::table('f01_indikator_nilai', function (Blueprint $table) {
            $table->boolean('is_changed')->default(false)->after('status')->comment('True jika indikator diubah oleh UPP pada saat resubmit');
        });

        Schema::table('f02_indikator_validasi', function (Blueprint $table) {
            $table->boolean('is_carried_over')->default(false)->after('status')->comment('True jika skor merupakan hasil carry-over otomatis dari versi sebelumnya');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('f01_indikator_nilai', function (Blueprint $table) {
            $table->dropColumn('is_changed');
        });

        Schema::table('f02_indikator_validasi', function (Blueprint $table) {
            $table->dropColumn('is_carried_over');
        });
    }
};
