<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnalyticsExportsTable extends Migration
{
    public function up()
    {
        Schema::create('analytics_exports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->string('scope_key')->nullable()->index();
            $table->string('idempotency_key')->nullable()->index();
            $table->string('correlation_id')->nullable()->index();
            $table->enum('type', ['csv', 'pdf']);
            $table->json('params')->nullable();
            $table->string('file_path')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->enum('status', ['pending','processing','ready','failed'])->default('pending')->index();
            $table->text('error_message')->nullable();
            $table->unsignedBigInteger('processed_rows')->default(0);
            $table->unsignedBigInteger('total_rows_estimate')->nullable();
            $table->decimal('progress_percent', 5, 2)->default(0.00);
            $table->unsignedInteger('idempotency_attempts')->default(0);
            $table->timestamp('last_attempted_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('analytics_exports');
    }
}
