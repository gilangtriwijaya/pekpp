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
        // Modify enum to include likert_5
        Schema::table('f03_indikator', function (Blueprint $table) {
            $table->enum('tipe_jawaban', ['text', 'radio', 'checkbox', 'dropdown', 'likert_5', 'rating', 'textarea'])
                  ->default('radio')
                  ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert enum back
        Schema::table('f03_indikator', function (Blueprint $table) {
            $table->enum('tipe_jawaban', ['text', 'radio', 'checkbox', 'dropdown', 'rating', 'textarea'])
                  ->default('radio')
                  ->change();
        });
    }
};
