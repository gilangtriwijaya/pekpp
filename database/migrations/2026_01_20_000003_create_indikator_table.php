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
        Schema::create('indikator', function (Blueprint $table) {
            $table->id();

            $table->foreignId('aspek_id')
                  ->constrained('aspek')
                  ->cascadeOnDelete();

            $table->string('kode', 50);
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->decimal('bobot', 5, 2)->default(0);
            $table->integer('urutan')->default(0);
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->unique(['aspek_id', 'kode']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('indikator');
    }
};
