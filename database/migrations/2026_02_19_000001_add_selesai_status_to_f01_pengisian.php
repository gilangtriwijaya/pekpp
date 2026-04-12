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
        Schema::table('f01_pengisian', function (Blueprint $table) {
            // Modify enum to add 'selesai' status
            // Need to recreate the column with new enum values
            $table->enum('status', ['draft', 'submitted', 'rolled_back', 'selesai'])
                ->default('draft')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('f01_pengisian', function (Blueprint $table) {
            // Revert to original enum values
            $table->enum('status', ['draft', 'submitted', 'rolled_back'])
                ->default('draft')
                ->change();
        });
    }
};
