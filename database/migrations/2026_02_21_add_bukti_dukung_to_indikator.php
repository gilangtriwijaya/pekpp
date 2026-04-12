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
        Schema::table('indikator', function (Blueprint $table) {
            // Add bukti_dukung column after deskripsi
            $table->text('bukti_dukung')->nullable()->after('deskripsi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('indikator', function (Blueprint $table) {
            $table->dropColumn('bukti_dukung');
        });
    }
};
