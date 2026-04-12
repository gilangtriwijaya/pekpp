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
        Schema::create('f03_indikator', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_id')->constrained('periode')->cascadeOnDelete();
            $table->foreignId('f03_aspek_id')->constrained('f03_aspek')->cascadeOnDelete();
            $table->string('kode', 50);
            $table->text('pertanyaan');
            $table->enum('tipe_jawaban', ['text', 'radio', 'checkbox', 'dropdown', 'rating', 'textarea'])->default('radio');
            $table->json('pilihan_jawaban')->nullable()->comment('Array of {value, label}');
            $table->integer('urutan')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['periode_id', 'f03_aspek_id', 'kode'], 'uq_f03_indikator_periode_aspek_kode');
            $table->index('f03_aspek_id');
            $table->index('periode_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('f03_indikator');
    }
};
