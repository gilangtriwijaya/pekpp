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
            $table->unsignedBigInteger('upp_id')->index();
            $table->unsignedBigInteger('aspek_id')->index();
            $table->unsignedBigInteger('indikator_id')->nullable()->index();
            $table->unsignedBigInteger('total_responses')->default(0);
            $table->decimal('avg_score', 6, 2)->nullable();
            $table->decimal('median_score', 6, 2)->nullable();
            $table->decimal('pct_validated', 5, 2)->nullable();
            $table->decimal('pct_empty', 5, 2)->nullable();
            // metadata
            $table->string('periode_label')->nullable();
            $table->string('aggregate_version')->nullable()->index();
            $table->string('dimension_hash')->nullable()->index();
            $table->timestamp('last_source_updated_at')->nullable();
            $table->string('scope_key')->nullable()->index();
            $table->timestamp('computed_at')->nullable();
            $table->timestamps();

            $table->unique(['periode_id','upp_id','aspek_id','indikator_id'], 'analytics_agg_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('analytics_aggregates');
    }
}
