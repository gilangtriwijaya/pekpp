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
        Schema::create('f03_pengisian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('upp_id')->constrained('upps')->cascadeOnDelete();
            $table->foreignId('periode_id')->constrained('periode')->cascadeOnDelete();
            $table->foreignId('f03_token_id')->constrained('f03_token')->cascadeOnDelete();
            $table->string('response_identifier', 64)->comment('Hash of IP + Fingerprint');
            $table->text('ip_address_hashed')->nullable()->comment('Encrypted IP for reference');
            $table->text('browser_fingerprint')->nullable();
            $table->timestamp('response_date');
            $table->boolean('is_duplicate')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index('f03_token_id');
            $table->index('response_identifier');
            $table->index('response_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('f03_pengisian');
    }
};
