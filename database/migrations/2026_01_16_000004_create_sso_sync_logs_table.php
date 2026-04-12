<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sso_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('command');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->string('status', 50)->nullable();
            $table->text('message')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sso_sync_logs');
    }
};
