<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnalyticsAggregatesTable extends Migration
{
    public function up()
    {
        Schema::create('analytics_aggregates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('periode_id')->index();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->string('scope_key')->nullable()->index();
            $table->enum('level', ['indicator','aspek','upp','opd','provinsi'])->default('indicator')->index();
            $table->string('dimension_hash')->nullable()->index();
            $table->unsignedInteger('aggregate_version')->default(1);
            $table->unsignedBigInteger('upp_id')->nullable()->index();
            $table->unsignedBigInteger('aspek_id')->nullable()->index();
            $table->unsignedBigInteger('indikator_id')->nullable()->index();
            $table->unsignedBigInteger('total_responses')->default(0);
            $table->decimal('avg_score', 6, 2)->default(0.00);
            $table->decimal('median_score', 6, 2)->default(0.00);
            $table->decimal('pct_validated', 5, 2)->default(0.00);
            $table->decimal('pct_empty', 5, 2)->default(0.00);
            $table->timestamp('computed_at')->nullable();
            $table->timestamps();
            $table->unique(['periode_id','scope_key','dimension_hash'], 'analytics_agg_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('analytics_aggregates');
    }
}
