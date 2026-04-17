<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsExport extends Model
{
    protected $table = 'analytics_exports';
    protected $guarded = [];

    protected $casts = [
        'params' => 'array',
        'processed_rows' => 'integer',
        'total_rows_estimate' => 'integer',
        'progress_percent' => 'decimal:2',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'last_attempted_at' => 'datetime',
    ];
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsExport extends Model
{
    protected $table = 'analytics_exports';

    protected $casts = [
        'params' => 'array',
        'correlation_id' => 'string',
    ];

    protected $fillable = [
        'user_id','type','params','file_path','file_size','status','error_message',
        'idempotency_key','correlation_id','processed_rows','total_rows_estimate','progress_percent','started_at','finished_at'
    ];
}
