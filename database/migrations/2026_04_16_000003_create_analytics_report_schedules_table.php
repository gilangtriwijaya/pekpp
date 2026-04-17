<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnalyticsReportSchedulesTable extends Migration
{
    public function up()
    {
        Schema::create('analytics_report_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->enum('frequency',['daily','weekly','monthly']);
            $table->json('params')->nullable();
            $table->boolean('enabled')->default(true);
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('next_run_at')->nullable()->index();
            $table->enum('last_run_status', ['success','failed','skipped'])->nullable();
            $table->unsignedBigInteger('last_export_id')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('analytics_report_schedules');
    }
}
