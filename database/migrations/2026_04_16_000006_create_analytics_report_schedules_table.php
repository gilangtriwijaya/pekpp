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
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('analytics_report_schedules');
    }
}
