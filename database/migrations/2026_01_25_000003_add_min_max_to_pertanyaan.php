<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pertanyaan')) {
            Schema::table('pertanyaan', function (Blueprint $table) {
                if (! Schema::hasColumn('pertanyaan', 'min')) {
                    $table->integer('min')->nullable()->after('aktif');
                }
                if (! Schema::hasColumn('pertanyaan', 'max')) {
                    $table->integer('max')->nullable()->after('min');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pertanyaan')) {
            Schema::table('pertanyaan', function (Blueprint $table) {
                if (Schema::hasColumn('pertanyaan', 'min')) {
                    $table->dropColumn('min');
                }
                if (Schema::hasColumn('pertanyaan', 'max')) {
                    $table->dropColumn('max');
                }
            });
        }
    }
};
