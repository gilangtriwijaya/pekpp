<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) f01_pengisian (header)
        Schema::create('f01_pengisian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_id')->constrained('periode')->cascadeOnDelete();
            $table->foreignId('upp_id')->constrained('upps')->cascadeOnDelete();
            $table->enum('status', ['draft','dikirim','diverifikasi','dikembalikan'])->default('draft');
            $table->timestamp('dikirim_pada')->nullable();
            $table->unsignedBigInteger('dikirim_oleh')->nullable();
            $table->text('catatan_umum')->nullable();
            $table->timestamps();

            $table->unique(['periode_id','upp_id'], 'uq_f01_periode_upp');

            $table->foreign('dikirim_oleh')->references('id')->on('users')->nullOnDelete();
        });

        // 2) f01_indikator_nilai (detail)
        Schema::create('f01_indikator_nilai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('f01_pengisian_id')->constrained('f01_pengisian')->cascadeOnDelete();
            $table->foreignId('indikator_id')->constrained('indikator')->cascadeOnDelete();
            $table->decimal('nilai', 8, 2)->nullable();
            $table->text('justifikasi')->nullable();
            $table->enum('status', ['draft','final'])->default('draft');
            $table->timestamps();

            $table->unique(['f01_pengisian_id','indikator_id'], 'uq_f01_pengisian_indikator');
        });

        // 3) f01_indikator_bukti (evidence attachments)
        Schema::create('f01_indikator_bukti', function (Blueprint $table) {
            $table->id();
            $table->foreignId('f01_indikator_nilai_id')->constrained('f01_indikator_nilai')->cascadeOnDelete();
            $table->enum('jenis', ['file','url'])->default('file');
            $table->string('nama')->nullable();
            $table->text('path_atau_url');
            $table->text('keterangan')->nullable();
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->timestamps();

            $table->foreign('uploaded_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('f01_indikator_bukti', function (Blueprint $table) {
            $table->dropForeign(['uploaded_by']);
        });
        Schema::dropIfExists('f01_indikator_bukti');

        Schema::table('f01_indikator_nilai', function (Blueprint $table) {
            // Laravel will drop foreign keys when dropping table, but drop explicitly if present
        });
        Schema::dropIfExists('f01_indikator_nilai');

        Schema::table('f01_pengisian', function (Blueprint $table) {
            $table->dropForeign(['dikirim_oleh']);
        });
        Schema::dropIfExists('f01_pengisian');
    }
};
