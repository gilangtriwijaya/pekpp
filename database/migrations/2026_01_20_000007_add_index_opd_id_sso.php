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
        Schema::table('upps', function (Blueprint $table) {
            if (! Schema::hasColumn('upps', 'opd_id_sso')) {
                // nothing to index
                return;
            }
            $table->index('opd_id_sso', 'upps_opd_id_sso_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('upps', function (Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexes = array_map(function ($i) { return $i->getName(); }, $sm->listTableIndexes('upps'));
            if (in_array('upps_opd_id_sso_index', $indexes, true) || in_array('opd_id_sso', $indexes, true)) {
                $table->dropIndex('upps_opd_id_sso_index');
            }
        });
    }
};
