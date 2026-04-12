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
        Schema::table('periode', function (Blueprint $table) {
            $table->enum('status_pengisian', ['open', 'locked', 'closed'])->default('open')->after('is_aktif')->comment('Status penerimaan input: open=terima input, locked=tidak terima tapi visible, closed=archived');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('periode', function (Blueprint $table) {
            $table->dropColumn('status_pengisian');
        });
    }
};
