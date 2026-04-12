<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_unit_roles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('opd_unit_id');
            $table->string('role');
            $table->timestamps();
            $table->unique(['user_id', 'opd_unit_id', 'role'], 'user_unit_role_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_unit_roles');
    }
};
