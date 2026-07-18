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
        Schema::create('pendataan_aspek', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_id')->constrained('periode')->onDelete('cascade');
            $table->string('kode', 20)->nullable();
            $table->string('nama');
            $table->integer('urutan')->default(0);
            $table->boolean('aktif')->default(true);
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('pendataan_pertanyaan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pendataan_aspek_id')->constrained('pendataan_aspek')->onDelete('cascade');
            $table->string('kode', 20)->nullable();
            $table->text('label');
            $table->string('tipe_input', 50)->default('radio'); // radio, text, number, dsb
            $table->json('opsi_jawaban')->nullable();
            $table->boolean('wajib')->default(true);
            $table->integer('urutan')->default(0);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('pendataan_pengisian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_id')->constrained('periode')->onDelete('cascade');
            $table->foreignId('upp_id')->constrained('upps')->onDelete('cascade');
            $table->string('status', 20)->default('draft'); // draft, final
            $table->foreignId('dikirim_oleh')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('pendataan_jawaban', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pendataan_pengisian_id')->constrained('pendataan_pengisian')->onDelete('cascade');
            $table->foreignId('pendataan_pertanyaan_id')->constrained('pendataan_pertanyaan')->onDelete('cascade');
            $table->text('nilai')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pendataan_jawaban');
        Schema::dropIfExists('pendataan_pengisian');
        Schema::dropIfExists('pendataan_pertanyaan');
        Schema::dropIfExists('pendataan_aspek');
    }
};
