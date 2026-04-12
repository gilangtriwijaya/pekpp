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
        Schema::create('f02_skors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('indikator_id')->constrained('indikator')->onDelete('cascade');
            $table->foreignId('periode_id')->constrained('periode')->onDelete('cascade');
            $table->text('skor_1')->nullable()->comment('Narasi untuk skor 1');
            $table->text('skor_2')->nullable()->comment('Narasi untuk skor 2');
            $table->text('skor_3')->nullable()->comment('Narasi untuk skor 3');
            $table->text('skor_4')->nullable()->comment('Narasi untuk skor 4');
            $table->text('skor_5')->nullable()->comment('Narasi untuk skor 5');
            $table->timestamps();
            $table->unique(['indikator_id', 'periode_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('f02_skors');
    }
};
