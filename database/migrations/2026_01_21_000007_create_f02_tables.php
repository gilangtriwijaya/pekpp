<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('f02_validasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('f01_pengisian_id')->constrained('f01_pengisian')->cascadeOnDelete();
            $table->enum('status', ['draft','selesai'])->default('draft');
            $table->timestamp('divalidasi_pada')->nullable();
            $table->unsignedBigInteger('divalidasi_oleh')->nullable();
            $table->text('catatan_umum')->nullable();
            $table->timestamps();

            $table->unique(['f01_pengisian_id'], 'uq_f02_f01_pengisian');
            $table->foreign('divalidasi_oleh')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('f02_indikator_validasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('f02_validasi_id')->constrained('f02_validasi')->cascadeOnDelete();
            $table->foreignId('indikator_id')->constrained('indikator')->cascadeOnDelete();
            $table->decimal('skor', 8, 2)->nullable();
            $table->enum('status', ['draft','final'])->default('draft');
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->unique(['f02_validasi_id','indikator_id'], 'uq_f02_validasi_indikator');
        });

        Schema::create('f02_catatan_indikator', function (Blueprint $table) {
            $table->id();
            $table->foreignId('f02_indikator_validasi_id')->constrained('f02_indikator_validasi')->cascadeOnDelete();
            $table->text('isi');
            $table->unsignedBigInteger('dibuat_oleh')->nullable();
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamps();

            $table->foreign('dibuat_oleh')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('f02_catatan_indikator', function (Blueprint $table) {
            $table->dropForeign(['dibuat_oleh']);
        });
        Schema::dropIfExists('f02_catatan_indikator');

        Schema::dropIfExists('f02_indikator_validasi');

        Schema::table('f02_validasi', function (Blueprint $table) {
            $table->dropForeign(['divalidasi_oleh']);
        });
        Schema::dropIfExists('f02_validasi');
    }
};
