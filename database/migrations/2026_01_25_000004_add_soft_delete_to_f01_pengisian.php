<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('f01_pengisian', 'deleted_at')) {
            Schema::table('f01_pengisian', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('f01_pengisian', 'deleted_at')) {
            Schema::table('f01_pengisian', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
