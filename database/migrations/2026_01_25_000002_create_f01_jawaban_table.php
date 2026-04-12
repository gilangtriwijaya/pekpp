<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('f01_jawaban', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('f01_pengisian_id');
            $table->unsignedBigInteger('pertanyaan_id');
            $table->text('nilai')->nullable();
            $table->timestamps();

            $table->unique(['f01_pengisian_id', 'pertanyaan_id'], 'f01_jawaban_pengisian_pert_unique');

            $table->foreign('f01_pengisian_id')->references('id')->on('f01_pengisian')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign('pertanyaan_id')->references('id')->on('pertanyaan')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('f01_jawaban', function (Blueprint $table) {
            $table->dropForeign(['f01_pengisian_id']);
            $table->dropForeign(['pertanyaan_id']);
        });
        Schema::dropIfExists('f01_jawaban');
    }
};
