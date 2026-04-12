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
        Schema::create('f03_jawaban', function (Blueprint $table) {
            $table->id();
            $table->foreignId('f03_pengisian_id')->constrained('f03_pengisian')->cascadeOnDelete();
            $table->foreignId('f03_indikator_id')->constrained('f03_indikator')->cascadeOnDelete();
            $table->unsignedTinyInteger('score')->comment('1-5 hardcoded');
            $table->text('catatan')->nullable()->comment('Optional response text');
            $table->timestamps();

            $table->index('f03_pengisian_id');
            $table->index('f03_indikator_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('f03_jawaban');
    }
};
