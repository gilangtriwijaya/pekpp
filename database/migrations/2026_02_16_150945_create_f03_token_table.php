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
        Schema::create('f03_token', function (Blueprint $table) {
            $table->id();
            $table->foreignId('upp_id')->constrained('upps')->cascadeOnDelete();
            $table->foreignId('periode_id')->constrained('periode')->cascadeOnDelete();
            $table->string('token', 64)->unique();
            $table->boolean('allow_multiple_responses')->default(false);
            $table->timestamp('expired_date')->nullable();
            $table->longText('qr_code')->nullable()->comment('SVG or PNG base64');
            $table->boolean('aktif')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['upp_id', 'periode_id'], 'uq_f03_token_upp_periode');
            $table->index('upp_id');
            $table->index('periode_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('f03_token');
    }
};
