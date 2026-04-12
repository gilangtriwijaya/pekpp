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
        Schema::table('pertanyaan', function (Blueprint $table) {
            $table->string('skip_if_answer')->nullable()->after('show_when')
                  ->comment('Jika dijawab dengan value ini, skip semua pertanyaan berikutnya dalam indikator yang sama');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pertanyaan', function (Blueprint $table) {
            $table->dropColumn('skip_if_answer');
        });
    }
};
