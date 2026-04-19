<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1) Copy existing `name` values into `nama` if `nama` is empty
        try {
            DB::statement("UPDATE users SET nama = name WHERE (nama IS NULL OR nama = '') AND (name IS NOT NULL AND name <> '');");
        } catch (\Exception $e) {
            // ignore if update fails
        }

        // 2) Drop the legacy `name` column safely
        try {
            // Skip dropping the legacy `name` column on SQLite (tests use in-memory SQLite)
            $driver = DB::getDriverName();
            if ($driver !== 'sqlite' && Schema::hasColumn('users', 'name')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->dropColumn('name');
                });
            }
        } catch (\Exception $e) {
            // ignore drop failure; admin can inspect
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate `name` column and copy back from `nama` where possible
        if (! Schema::hasColumn('users', 'name')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('name')->nullable()->after('nama');
            });

            try {
                DB::statement("UPDATE users SET name = nama WHERE (name IS NULL OR name = '') AND (nama IS NOT NULL AND nama <> '');");
            } catch (\Exception $e) {
                // ignore
            }
        }
    }
};
