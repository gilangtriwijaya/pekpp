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
        Schema::create('f01_bukti_dukung', function (Blueprint $table) {
            $table->id();
            $table->foreignId('f01_pengisian_id')->constrained('f01_pengisian')->onDelete('cascade');
            $table->foreignId('indikator_id')->constrained('indikator')->onDelete('cascade');
            $table->text('url_bukti')->nullable();
            $table->timestamps();
            
            // Unique constraint: one URL per indikator per pengisian
            $table->unique(['f01_pengisian_id', 'indikator_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('f01_bukti_dukung');
    }
};
