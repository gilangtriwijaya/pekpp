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
        Schema::table('f02_validasi', function (Blueprint $table) {
            // Add updated_by field to track who last updated the validation skor
            $table->unsignedBigInteger('updated_by')->nullable()->after('divalidasi_oleh');
            
            // Add foreign key constraint
            $table->foreign('updated_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('f02_validasi', function (Blueprint $table) {
            $table->dropForeignKeyIfExists(['updated_by']);
            $table->dropColumn('updated_by');
        });
    }
};
