<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsAggregate extends Model
{
    protected $table = 'analytics_aggregates';
    protected $guarded = [];

    protected $casts = [
        'total_responses' => 'integer',
        'avg_score' => 'decimal:2',
        'median_score' => 'decimal:2',
        'pct_validated' => 'decimal:2',
        'pct_empty' => 'decimal:2',
        'computed_at' => 'datetime',
    ];

    protected $fillable = [
        'periode_id','periode_label','tenant_id','scope_key','upp_id','aspek_id','indikator_id','total_responses',
        'avg_score','median_score','pct_validated','pct_empty','computed_at','aggregate_version','dimension_hash','last_source_updated_at'
    ];
}
