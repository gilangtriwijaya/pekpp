<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('f02_validasi', function (Blueprint $table) {
            if (!Schema::hasColumn('f02_validasi', 'total_nilai')) {
                $table->decimal('total_nilai', 5, 2)->nullable()->after('catatan_umum');
            }
        });
    }

    public function down(): void
    {
        Schema::table('f02_validasi', function (Blueprint $table) {
            if (Schema::hasColumn('f02_validasi', 'total_nilai')) {
                $table->dropColumn('total_nilai');
            }
        });
    }
};
