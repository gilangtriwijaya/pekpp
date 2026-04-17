<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('analytics_export_quota', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->date('date')->index();
            $table->unsignedInteger('count')->default(0);
            $table->timestamps();
            $table->unique(['user_id','tenant_id','date'], 'analytics_export_quota_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('analytics_export_quota');
    }
};
