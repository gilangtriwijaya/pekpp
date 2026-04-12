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
        // Check if periode_id column already exists
        if (!\Schema::hasColumn('f02_validasi', 'periode_id')) {
            Schema::table('f02_validasi', function (Blueprint $table) {
                // Add periode_id column after f01_pengisian_id
                $table->unsignedBigInteger('periode_id')->nullable()->after('f01_pengisian_id');
                $table->foreign('periode_id')->references('id')->on('periode')->cascadeOnDelete();
                
                // Index for faster queries
                $table->index('periode_id');
            });

            // Populate periode_id from f01_pengisian
            \DB::statement('
                UPDATE f02_validasi fv
                JOIN f01_pengisian fp ON fv.f01_pengisian_id = fp.id
                SET fv.periode_id = fp.periode_id
                WHERE fv.periode_id IS NULL
            ');

            // Make periode_id NOT NULL after populating
            Schema::table('f02_validasi', function (Blueprint $table) {
                $table->unsignedBigInteger('periode_id')->nullable(false)->change();
            });
        } else {
            // Column already exists, just ensure it's properly indexed and foreign keyed
            if (!\Schema::hasColumn('f02_validasi', 'periode_id')) {
                Schema::table('f02_validasi', function (Blueprint $table) {
                    // Just add index/FK if missing
                    $table->index('periode_id');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('f02_validasi', function (Blueprint $table) {
            $table->dropForeign(['periode_id']);
            $table->dropIndex(['periode_id']);
            $table->dropColumn('periode_id');
        });
    }
};
