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
        Schema::table('f02_skors', function (Blueprint $table) {
            // Add skor_0 as the first score level (before skor_1)
            $table->text('skor_0')->nullable()->after('periode_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('f02_skors', function (Blueprint $table) {
            $table->dropColumn('skor_0');
        });
    }
};
