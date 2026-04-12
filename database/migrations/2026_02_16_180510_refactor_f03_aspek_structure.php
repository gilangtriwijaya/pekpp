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
        // Refactor F03 Aspek table
        Schema::table('f03_aspek', function (Blueprint $table) {
            // Drop unnecessary columns
            if (Schema::hasColumn('f03_aspek', 'domain')) {
                $table->dropColumn('domain');
            }
            if (Schema::hasColumn('f03_aspek', 'target_responden')) {
                $table->dropColumn('target_responden');
            }
            // Add bobot column if not exists
            if (!Schema::hasColumn('f03_aspek', 'bobot')) {
                $table->decimal('bobot', 5, 2)->default(0)->after('urutan');
            }
        });

        // Add target_responden_f03 to periode table
        Schema::table('periode', function (Blueprint $table) {
            if (!Schema::hasColumn('periode', 'target_responden_f03')) {
                $table->integer('target_responden_f03')->default(0)->after('tahun');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert F03 Aspek table
        Schema::table('f03_aspek', function (Blueprint $table) {
            // Add back removed columns
            if (!Schema::hasColumn('f03_aspek', 'domain')) {
                $table->string('domain')->nullable()->after('nama');
            }
            if (!Schema::hasColumn('f03_aspek', 'target_responden')) {
                $table->integer('target_responden')->default(0)->after('domain');
            }
            // Drop bobot column if exists
            if (Schema::hasColumn('f03_aspek', 'bobot')) {
                $table->dropColumn('bobot');
            }
        });

        // Remove target_responden_f03 from periode table
        Schema::table('periode', function (Blueprint $table) {
            if (Schema::hasColumn('periode', 'target_responden_f03')) {
                $table->dropColumn('target_responden_f03');
            }
        });
    }
};
