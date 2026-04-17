<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsReportSchedule extends Model
{
    protected $table = 'analytics_report_schedules';
    protected $guarded = [];

    protected $casts = [
        'params' => 'array',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
    ];
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsReportSchedule extends Model
{
    protected $table = 'analytics_report_schedules';

    protected $casts = [
        'params' => 'array',
    ];

    protected $fillable = ['name','user_id','frequency','params','enabled'];
}
