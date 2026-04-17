<?php

namespace App\Jobs\Analytics;

use App\Models\AnalyticsExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\Analytics\AnalyticsReadService;
use Illuminate\Support\Facades\Storage;
use App\Jobs\SendAnalyticsExportNotificationJob;

class GenerateAnalyticsCsvJob implements ShouldQueue
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
        $export = AnalyticsExport::find($this->exportId);
        if (!$export) {
            return;
        }

        $export->update(['status' => 'processing', 'started_at' => now(), 'last_attempted_at' => now()]);

        $disk = config('analytics.storage_disk', 'local');
        $relPath = 'exports/analytics/'.now()->format('Y/m').'/'.$export->id.'.csv';
        $localPath = storage_path('app/'.$relPath);

        // ensure directory
        if (!is_dir(dirname($localPath))) {
            mkdir(dirname($localPath), 0755, true);
        }

        $fp = fopen($localPath, 'w');
        if ($fp === false) {
            $export->update(['status' => 'failed', 'error_message' => 'cannot_open_local_file']);
            return;
        }

        // header
        fputcsv($fp, ['periode_id','upp_id','upp_nama','aspek_id','aspek_nama','indikator_id','indikator_nama','total_responses','avg_score','median_score','pct_validated']);

        $read = app(AnalyticsReadService::class);
        $query = $read->buildAggregateQuery($export->params ?? []);
        $query->orderBy('id');

        $processed = 0;
        $updateEvery = config('analytics.progress_update_every_rows', 1000);

        try {
            $query->chunkById(1000, function ($rows) use ($fp, &$processed, $export, $updateEvery) {
                foreach ($rows as $row) {
                    fputcsv($fp, [
                        $row->periode_id,
                        $row->upp_id,
                        $row->periode_label ?? null,
                        $row->aspek_id,
                        null,
                        $row->indikator_id,
                        null,
                        $row->total_responses,
                        $row->avg_score,
                        $row->median_score,
                        $row->pct_validated,
                    ]);
                    $processed++;
                }

                if ($processed > 0 && $processed % $updateEvery === 0) {
                    $export->fresh()->update([
                        'processed_rows' => $processed,
                        'progress_percent' => $this->computeProgress($processed, $export->total_rows_estimate),
                        'last_attempted_at' => now(),
                    ]);
                }
            });

            fclose($fp);

            // upload to configured disk if not local
            if ($disk !== 'local') {
                $stream = fopen($localPath, 'r');
                Storage::disk($disk)->put($relPath, $stream);
                if (is_resource($stream)) {
                    fclose($stream);
                }
                // remove local temp file
                @unlink($localPath);
            }

            $fileSize = Storage::disk($disk)->exists($relPath) ? Storage::disk($disk)->size($relPath) : (file_exists($localPath) ? filesize($localPath) : 0);

            $export->update([
                'file_path' => $relPath,
                'file_size' => $fileSize,
                'status' => 'ready',
                'processed_rows' => $processed,
                'progress_percent' => 100.00,
                'finished_at' => now(),
            ]);

            // notify
            SendAnalyticsExportNotificationJob::dispatch($export->id);
        } catch (\Throwable $e) {
            if (is_resource($fp)) {
                fclose($fp);
            }
            $export->update(['status' => 'failed', 'error_message' => $e->getMessage(), 'last_attempted_at' => now()]);
            throw $e;
        }
    }

    public function middleware()
    {
        return [new \App\Jobs\Middleware\EnsureJobHasScope()];
    }

    protected function computeProgress(int $processed, $estimate): float
    {
        $total = $estimate ?: max(1, $processed);
        return min(100.0, round(($processed / $total) * 100.0, 2));
    }
}
