<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add database-level protection for is_aktif to ensure only 1 periode can be active
     * Uses MariaDB TRIGGER since it doesn't support conditional unique indexes
     */
    public function up(): void
    {
        // Create BEFORE INSERT trigger to prevent multiple active periods
        DB::statement("
            CREATE TRIGGER trigger_periode_insert_aktif BEFORE INSERT ON periode
            FOR EACH ROW
            BEGIN
                IF NEW.is_aktif = 1 THEN
                    IF (SELECT COUNT(*) FROM periode WHERE is_aktif = 1) > 0 THEN
                        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Only one periode can be active at a time';
                    END IF;
                END IF;
            END
        ");

        // Create BEFORE UPDATE trigger to prevent multiple active periods
        DB::statement("
            CREATE TRIGGER trigger_periode_update_aktif BEFORE UPDATE ON periode
            FOR EACH ROW
            BEGIN
                IF NEW.is_aktif = 1 AND OLD.is_aktif = 0 THEN
                    IF (SELECT COUNT(*) FROM periode WHERE is_aktif = 1) > 0 THEN
                        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Only one periode can be active at a time';
                    END IF;
                END IF;
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS trigger_periode_insert_aktif');
        DB::statement('DROP TRIGGER IF EXISTS trigger_periode_update_aktif');
    }
};
