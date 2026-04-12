<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aspek', function (Blueprint $table) {
            if (!Schema::hasColumn('aspek', 'bobot')) {
                $table->unsignedTinyInteger('bobot')->default(0)->comment('Bobot persentase (1-100)')->after('urutan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('aspek', function (Blueprint $table) {
            if (Schema::hasColumn('aspek', 'bobot')) {
                $table->dropColumn('bobot');
            }
        });
    }
};
