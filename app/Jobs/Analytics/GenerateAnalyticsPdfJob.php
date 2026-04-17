<?php

namespace App\Jobs\Analytics;

use App\Models\AnalyticsExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\Analytics\AnalyticsPdfService;
use App\Jobs\SendAnalyticsExportNotificationJob;
use App\Services\Analytics\Metrics\AnalyticsMetrics;

class GenerateAnalyticsPdfJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $exportId;
    public $tries = 3;

    public function __construct(int $exportId)
    {
        $this->exportId = $exportId;
    }

    public function handle(AnalyticsPdfService $pdfService)
    {
        $metrics = app(AnalyticsMetrics::class);
        $start = microtime(true);

        $export = AnalyticsExport::find($this->exportId);
        if (! $export) return;

        $export->update(['status' => 'processing', 'started_at' => now(), 'last_attempted_at' => now()]);

        $disk = config('analytics.storage_disk', 'local');
        $relPath = 'exports/analytics/'.now()->format('Y/m').'/'.$export->id.'.pdf';

        try {
            // choose view and data from export params
            $view = $export->params['view'] ?? 'analytics.pdf.report';
            $data = $export->params['data'] ?? [];

            $pdfService->renderPdf($view, $data, $relPath);

            $fileSize = \Illuminate\Support\Facades\Storage::disk($disk)->exists($relPath) ? \Illuminate\Support\Facades\Storage::disk($disk)->size($relPath) : (file_exists(storage_path('app/'.$relPath)) ? filesize(storage_path('app/'.$relPath)) : 0);

            $export->update([
                'file_path' => $relPath,
                'file_size' => $fileSize,
                'status' => 'ready',
                'processed_rows' => 0,
                'progress_percent' => 100.00,
                'finished_at' => now(),
            ]);
            $duration = microtime(true) - $start;
            $metrics->observeExportDuration('pdf', $duration);
            $metrics->incrementExportSucceeded('pdf');
            SendAnalyticsExportNotificationJob::dispatch($export->id);
        } catch (\Throwable $e) {
            $export->update(['status' => 'failed', 'error_message' => $e->getMessage(), 'last_attempted_at' => now()]);
            $metrics->incrementExportFailed('pdf');
            throw $e;
        }
    }
}
<?php

namespace App\Jobs\Analytics;

use App\Models\AnalyticsExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateAnalyticsPdfJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public $exportId;
    public $tries = 5;

    public function __construct(int $exportId)
    {
        $this->exportId = $exportId;
    }

    public function handle()
    {
        // TODO: render blade to HTML, convert to PDF via snappy/dompdf, update AnalyticsExport
    }
}
