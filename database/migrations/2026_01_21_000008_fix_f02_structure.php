<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Remove domain-specific duplicate timestamp column and keep Laravel timestamps
        if (Schema::hasColumn('f02_catatan_indikator', 'dibuat_pada')) {
            Schema::table('f02_catatan_indikator', function (Blueprint $table) {
                $table->dropColumn('dibuat_pada');
            });
        }

        // No index removals performed automatically because existing FK/index layout
        // is required by MySQL (composite unique keys do not substitute single-column
        // FK indexes unless the FK column is the leftmost). We keep indexes intact
        // to avoid data integrity issues.
    }

    public function down(): void
    {
        if (! Schema::hasColumn('f02_catatan_indikator', 'dibuat_pada')) {
            Schema::table('f02_catatan_indikator', function (Blueprint $table) {
                $table->timestamp('dibuat_pada')->nullable()->after('dibuat_oleh');
            });
        }
    }
};
