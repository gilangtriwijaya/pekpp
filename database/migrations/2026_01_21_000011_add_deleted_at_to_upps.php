<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('upps') && ! Schema::hasColumn('upps', 'deleted_at')) {
            Schema::table('upps', function (Blueprint $table) {
                $table->timestamp('deleted_at')->nullable()->after('updated_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('upps') && Schema::hasColumn('upps', 'deleted_at')) {
            Schema::table('upps', function (Blueprint $table) {
                $table->dropColumn('deleted_at');
            });
        }
    }
};
