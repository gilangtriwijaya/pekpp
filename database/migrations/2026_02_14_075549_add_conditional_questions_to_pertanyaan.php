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
            // Add parent question relationship for conditional questions
            $table->unsignedBigInteger('parent_pertanyaan_id')->nullable()->after('indikator_id');
            // Add condition for when to show this question (only for child questions)
            $table->enum('show_when', ['ya', 'tidak', 'keduanya'])->default('keduanya')->after('parent_pertanyaan_id');
            
            // Add foreign key constraint
            $table->foreign('parent_pertanyaan_id')
                ->references('id')
                ->on('pertanyaan')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pertanyaan', function (Blueprint $table) {
            $table->dropForeign(['parent_pertanyaan_id']);
            $table->dropColumn(['parent_pertanyaan_id', 'show_when']);
        });
    }
};
