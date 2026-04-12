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
        Schema::create('f03_response_demographics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('f03_pengisian_id')->unique();
            $table->foreign('f03_pengisian_id')->references('id')->on('f03_pengisian')->onDelete('cascade');
            $table->enum('gender', ['M', 'F', 'O', 'Prefer Not to Say'])->nullable();
            $table->unsignedSmallInteger('age')->nullable();
            $table->string('last_education')->nullable();
            $table->string('occupation')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('f03_response_demographics');
    }
};
