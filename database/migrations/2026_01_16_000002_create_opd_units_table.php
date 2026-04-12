<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('opd_units', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sso_id')->nullable()->unique();
            $table->unsignedBigInteger('opd_id')->nullable();
            $table->string('nama');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opd_units');
    }
};
