<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Convert all timestamps from UTC to WIB (UTC+7)
     * This adds 7 hours to all existing timestamps to reflect proper WIB time
     */
    public function up(): void
    {
        $driver = DB::getDriverName();
        if ($driver !== 'sqlite') {
            // Convert activity_logs timestamps
            DB::statement('UPDATE activity_logs SET created_at = DATE_ADD(created_at, INTERVAL 7 HOUR)');
            DB::statement('UPDATE activity_logs SET updated_at = DATE_ADD(updated_at, INTERVAL 7 HOUR)');

            // Convert f01_pengisian timestamps
            DB::statement('UPDATE f01_pengisian SET created_at = DATE_ADD(created_at, INTERVAL 7 HOUR)');
            DB::statement('UPDATE f01_pengisian SET updated_at = DATE_ADD(updated_at, INTERVAL 7 HOUR)');

            // Convert f02_validasi timestamps
            DB::statement('UPDATE f02_validasi SET created_at = DATE_ADD(created_at, INTERVAL 7 HOUR)');
            DB::statement('UPDATE f02_validasi SET updated_at = DATE_ADD(updated_at, INTERVAL 7 HOUR)');
            DB::statement('UPDATE f02_validasi SET divalidasi_pada = DATE_ADD(divalidasi_pada, INTERVAL 7 HOUR) WHERE divalidasi_pada IS NOT NULL');

            // Convert f03_respon timestamps
            DB::statement('UPDATE f03_respon SET created_at = DATE_ADD(created_at, INTERVAL 7 HOUR) WHERE created_at IS NOT NULL');
            DB::statement('UPDATE f03_respon SET updated_at = DATE_ADD(updated_at, INTERVAL 7 HOUR) WHERE updated_at IS NOT NULL');

            // Convert f03_pengisian timestamps
            DB::statement('UPDATE f03_pengisian SET created_at = DATE_ADD(created_at, INTERVAL 7 HOUR) WHERE created_at IS NOT NULL');
            DB::statement('UPDATE f03_pengisian SET updated_at = DATE_ADD(updated_at, INTERVAL 7 HOUR) WHERE updated_at IS NOT NULL');

            // Convert periode timestamps
            DB::statement('UPDATE periode SET created_at = DATE_ADD(created_at, INTERVAL 7 HOUR)');
            DB::statement('UPDATE periode SET updated_at = DATE_ADD(updated_at, INTERVAL 7 HOUR)');

            // Convert f01_jawaban timestamps
            DB::statement('UPDATE f01_jawaban SET created_at = DATE_ADD(created_at, INTERVAL 7 HOUR) WHERE created_at IS NOT NULL');
            DB::statement('UPDATE f01_jawaban SET updated_at = DATE_ADD(updated_at, INTERVAL 7 HOUR) WHERE updated_at IS NOT NULL');

            // Convert f02_indikator_validasi timestamps
            DB::statement('UPDATE f02_indikator_validasi SET created_at = DATE_ADD(created_at, INTERVAL 7 HOUR) WHERE created_at IS NOT NULL');
            DB::statement('UPDATE f02_indikator_validasi SET updated_at = DATE_ADD(updated_at, INTERVAL 7 HOUR) WHERE updated_at IS NOT NULL');

            // Convert f03_jawaban timestamps
            DB::statement('UPDATE f03_jawaban SET created_at = DATE_ADD(created_at, INTERVAL 7 HOUR) WHERE created_at IS NOT NULL');
            DB::statement('UPDATE f03_jawaban SET updated_at = DATE_ADD(updated_at, INTERVAL 7 HOUR) WHERE updated_at IS NOT NULL');

            // Convert users timestamps
            DB::statement('UPDATE users SET created_at = DATE_ADD(created_at, INTERVAL 7 HOUR)');
            DB::statement('UPDATE users SET updated_at = DATE_ADD(updated_at, INTERVAL 7 HOUR)');
            DB::statement('UPDATE users SET last_sync_at = DATE_ADD(last_sync_at, INTERVAL 7 HOUR) WHERE last_sync_at IS NOT NULL');
        }
    }

    /**
     * Revert the conversion (subtract 7 hours)
     */
    public function down(): void
    {
        $driver = DB::getDriverName();
        if ($driver !== 'sqlite') {
            DB::statement('UPDATE activity_logs SET created_at = DATE_SUB(created_at, INTERVAL 7 HOUR)');
            DB::statement('UPDATE activity_logs SET updated_at = DATE_SUB(updated_at, INTERVAL 7 HOUR)');

            DB::statement('UPDATE f01_pengisian SET created_at = DATE_SUB(created_at, INTERVAL 7 HOUR)');
            DB::statement('UPDATE f01_pengisian SET updated_at = DATE_SUB(updated_at, INTERVAL 7 HOUR)');

            DB::statement('UPDATE f02_validasi SET created_at = DATE_SUB(created_at, INTERVAL 7 HOUR)');
            DB::statement('UPDATE f02_validasi SET updated_at = DATE_SUB(updated_at, INTERVAL 7 HOUR)');
            DB::statement('UPDATE f02_validasi SET divalidasi_pada = DATE_SUB(divalidasi_pada, INTERVAL 7 HOUR) WHERE divalidasi_pada IS NOT NULL');

            DB::statement('UPDATE f03_respon SET created_at = DATE_SUB(created_at, INTERVAL 7 HOUR) WHERE created_at IS NOT NULL');
            DB::statement('UPDATE f03_respon SET updated_at = DATE_SUB(updated_at, INTERVAL 7 HOUR) WHERE updated_at IS NOT NULL');

            DB::statement('UPDATE f03_pengisian SET created_at = DATE_SUB(created_at, INTERVAL 7 HOUR) WHERE created_at IS NOT NULL');
            DB::statement('UPDATE f03_pengisian SET updated_at = DATE_SUB(updated_at, INTERVAL 7 HOUR) WHERE updated_at IS NOT NULL');

            DB::statement('UPDATE periode SET created_at = DATE_SUB(created_at, INTERVAL 7 HOUR)');
            DB::statement('UPDATE periode SET updated_at = DATE_SUB(updated_at, INTERVAL 7 HOUR)');

            DB::statement('UPDATE f01_jawaban SET created_at = DATE_SUB(created_at, INTERVAL 7 HOUR) WHERE created_at IS NOT NULL');
            DB::statement('UPDATE f01_jawaban SET updated_at = DATE_SUB(updated_at, INTERVAL 7 HOUR) WHERE updated_at IS NOT NULL');

            DB::statement('UPDATE f02_indikator_validasi SET created_at = DATE_SUB(created_at, INTERVAL 7 HOUR) WHERE created_at IS NOT NULL');
            DB::statement('UPDATE f02_indikator_validasi SET updated_at = DATE_SUB(updated_at, INTERVAL 7 HOUR) WHERE updated_at IS NOT NULL');

            DB::statement('UPDATE f03_jawaban SET created_at = DATE_SUB(created_at, INTERVAL 7 HOUR) WHERE created_at IS NOT NULL');
            DB::statement('UPDATE f03_jawaban SET updated_at = DATE_SUB(updated_at, INTERVAL 7 HOUR) WHERE updated_at IS NOT NULL');

            DB::statement('UPDATE users SET created_at = DATE_SUB(created_at, INTERVAL 7 HOUR)');
            DB::statement('UPDATE users SET updated_at = DATE_SUB(updated_at, INTERVAL 7 HOUR)');
            DB::statement('UPDATE users SET last_sync_at = DATE_SUB(last_sync_at, INTERVAL 7 HOUR) WHERE last_sync_at IS NOT NULL');
        }
    }
};
