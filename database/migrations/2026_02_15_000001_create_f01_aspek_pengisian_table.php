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
        Schema::create('f01_aspek_pengisian', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('f01_pengisian_id');
            $table->unsignedBigInteger('aspek_id');
            $table->enum('status', ['draft', 'tersimpan', 'divalidasi'])->default('draft');
            $table->timestamp('last_saved_at')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('f01_pengisian_id')
                ->references('id')
                ->on('f01_pengisian')
                ->onDelete('cascade');

            $table->foreign('aspek_id')
                ->references('id')
                ->on('aspek')
                ->onDelete('cascade');

            // Unique constraint: one status per pengisian-aspek pair
            $table->unique(['f01_pengisian_id', 'aspek_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('f01_aspek_pengisian');
    }
};
