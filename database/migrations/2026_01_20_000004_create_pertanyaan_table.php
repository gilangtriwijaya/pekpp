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
        Schema::create('pertanyaan', function (Blueprint $table) {
            $table->id();

            $table->foreignId('indikator_id')
                  ->constrained('indikator')
                  ->cascadeOnDelete();

            $table->string('kode', 50);
            $table->text('label');
            $table->enum('tipe_input', [
                'text',
                'textarea',
                'number',
                'radio',
                'checkbox',
                'select',
                'yesno'
            ]);
            $table->json('opsi_jawaban')->nullable();
            $table->boolean('wajib')->default(false);
            $table->integer('urutan')->default(0);
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->unique(['indikator_id', 'kode']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pertanyaan');
    }
};
