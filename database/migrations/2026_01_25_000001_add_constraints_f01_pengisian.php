<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // add unique index on upp_id + periode_id if not exists
        try {
            Schema::table('f01_pengisian', function (Blueprint $table) {
                $table->unique(['upp_id', 'periode_id'], 'f01_pengisian_upp_periode_unique');
            });
        } catch (\Throwable $e) {
            // ignore if already exists
        }

        // ensure status enum exists with default 'draft' (best effort)
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            try {
                DB::statement("ALTER TABLE f01_pengisian MODIFY COLUMN status ENUM('draft','submitted','rolled_back') NOT NULL DEFAULT 'draft'");
            } catch (\Throwable $e) {
                // ignore DB engines that don't support or if column already matches
            }
        }
    }

    public function down(): void
    {
        try {
            Schema::table('f01_pengisian', function (Blueprint $table) {
                $table->dropUnique('f01_pengisian_upp_periode_unique');
            });
        } catch (\Throwable $e) {
            // ignore
        }

        // cannot easily revert enum safely
    }
};
