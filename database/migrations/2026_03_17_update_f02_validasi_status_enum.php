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
            // Update enum to include 'dalam_proses' status
            // To modify enum in MySQL, we need to drop and recreate the column
            $table->enum('status', ['draft', 'dalam_proses', 'selesai'])->default('draft')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('f02_validasi', function (Blueprint $table) {
            // Revert to original enum
            $table->enum('status', ['draft', 'selesai'])->default('draft')->change();
        });
    }
};
