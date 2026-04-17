<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsExport;
use App\Services\Analytics\AnalyticsExportService;
use App\Services\Analytics\AnalyticsReadService;
use App\Jobs\Analytics\GenerateAnalyticsCsvJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnalyticsExportController extends Controller
{
    public function store(Request $request, AnalyticsExportService $service)
    {
        $idempotency = $request->header('Idempotency-Key');
        if (empty($idempotency)) {
            return response()->json(['message' => 'Idempotency-Key header required'], 422);
        }

        $params = $request->all();
        $scopeContext = $params['scope_context'] ?? null;
        $type = $params['type'] ?? 'csv';

        // For CSV, decide sync vs queued using estimate; for PDF always queue
        $estimate = $type === 'csv' ? $service->estimateRows(['scope_context' => $scopeContext, 'filters' => $params['filters'] ?? null]) : null;

        $effectiveThreshold = (int) floor(config('analytics.sync_threshold', 50000) * 0.8);

        // Synchronous path for small CSV exports
        if ($type === 'csv' && $estimate !== null && $estimate <= $effectiveThreshold) {
            $readService = app(AnalyticsReadService::class);
            return $this->streamCsvResponse($readService, $params);
        }

        // Enforce rate limits and create/export record (idempotent)
        try {
            $roles = [];
            if ($request->user() && method_exists($request->user(), 'getRoleNames')) {
                $roles = $request->user()->getRoleNames()->toArray();
            }

            $service->checkRateLimits($request->user()?->id, $scopeContext['tenant_id'] ?? null, $roles);

            $export = $service->createExportRecord([
                'user_id' => $request->user()?->id,
                'tenant_id' => $scopeContext['tenant_id'] ?? null,
                'scope_key' => $scopeContext['scope_key'] ?? null,
                'idempotency_key' => $idempotency,
                'correlation_id' => $params['correlation_id'] ?? null,
                'type' => $type,
                'params' => $params,
                'status' => 'pending',
                'total_rows_estimate' => $estimate,
            ]);

            // Dispatch queued job depending on type
            if ($type === 'csv') {
                GenerateAnalyticsCsvJob::dispatch($export->id);
            } else {
                \App\Jobs\Analytics\GenerateAnalyticsPdfJob::dispatch($export->id);
            }

            return response()->json(['message' => 'queued', 'export_id' => $export->id], 202);
        } catch (\RuntimeException $e) {
            $msg = $e->getMessage();
            if ($msg === 'user_rate_limit_exceeded' || $msg === 'tenant_rate_limit_exceeded') {
                return response()->json(['message' => 'rate_limited'], 429);
            }
            if ($msg === 'idempotency_existing_failed') {
                return response()->json(['message' => 'idempotency_conflict'], 409);
            }
            return response()->json(['message' => 'error', 'detail' => $msg], 500);
        }
    }

    protected function streamCsvResponse(AnalyticsReadService $readService, array $params)
    {
        $fileName = 'analytics_export_'.now()->format('Ymd_His').'.csv';
        $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=\"$fileName\""];

        $callback = function () use ($readService, $params) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['periode_id','upp_id','upp_nama','aspek_id','aspek_nama','indikator_id','indikator_nama','total_responses','avg_score','median_score','pct_validated']);

            $query = $readService->buildAggregateQuery($params);
            // prefer chunkById when id stable
            $query->orderBy('id');
            $query->chunkById(1000, function ($rows) use ($handle) {
                foreach ($rows as $row) {
                    fputcsv($handle, [
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
                }
            });
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function status($id)
    {
        $export = AnalyticsExport::findOrFail($id);
        return response()->json($export);
    }

    public function download($id)
    {
        $export = AnalyticsExport::findOrFail($id);

        // Authorization: ensure user can download
        $this->authorize('download', $export);

        if (empty($export->file_path)) {
            return response()->json(['message' => 'file_not_ready'], 404);
        }

        $disk = config('analytics.storage_disk', 'local');
        if ($disk === 's3') {
            $url = Storage::disk('s3')->temporaryUrl($export->file_path, now()->addHours(config('analytics.export_ttl_hours', 48)));
            return response()->json(['url' => $url]);
        }

        // Local streaming
        if (!Storage::disk($disk)->exists($export->file_path)) {
            return response()->json(['message' => 'file_missing'], 404);
        }

        $stream = Storage::disk($disk)->readStream($export->file_path);
        return response()->stream(function () use ($stream) {
            fpassthru($stream);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="analytics_export.csv"',
        ]);
    }

    public function retry(Request $request, $id, AnalyticsExportService $service)
    {
        $export = AnalyticsExport::findOrFail($id);
        $this->authorize('download', $export);

        if ($export->status !== 'failed') {
            return response()->json(['message' => 'cannot_retry_non_failed_export'], 409);
        }

        $export->increment('idempotency_attempts');
        $export->update(['status' => 'pending', 'last_attempted_at' => now()]);
        // re-dispatch job
        \App\Jobs\Analytics\GenerateAnalyticsCsvJob::dispatch($export->id);

        return response()->json(['message' => 'requeued', 'export_id' => $export->id], 202);
    }
}
