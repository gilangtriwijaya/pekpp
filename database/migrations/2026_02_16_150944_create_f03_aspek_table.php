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
        if (!Schema::hasTable('f03_aspek')) {
            Schema::create('f03_aspek', function (Blueprint $table) {
                $table->id();
                $table->foreignId('periode_id')->constrained('periode')->cascadeOnDelete();
                $table->string('kode', 50);
                $table->string('nama', 255);
                $table->enum('domain', ['internal', 'publik'])->default('publik');
                $table->integer('urutan')->nullable();
                $table->integer('target_responden')->default(0)->comment('Min responden untuk F03 valid');
                $table->boolean('aktif')->default(true);
                $table->text('keterangan')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->unique(['periode_id', 'kode'], 'uq_f03_aspek_periode_kode');
                $table->index('periode_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('f03_aspek');
    }
};
