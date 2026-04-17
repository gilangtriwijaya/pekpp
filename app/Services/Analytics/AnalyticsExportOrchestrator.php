<?php

namespace App\Services\Analytics;

use App\Models\AnalyticsExport;
use Illuminate\Support\Facades\Storage;

class AnalyticsExportOrchestrator
{
    public function handleCsvRequest($user, array $params)
    {
        // TODO: estimate rows, decide sync vs queue, create AnalyticsExport record or stream
        return response()->json(['status' => 'not_implemented'], 501);
    }

    public function handlePdfRequest($user, array $params)
    {
        // TODO: create export record and dispatch GenerateAnalyticsPdfJob
        return response()->json(['status' => 'queued'], 202);
    }

    public function downloadExport(AnalyticsExport $export)
    {
        $disk = config('analytics.storage_disk', 'local');
        if ($disk === 's3') {
            return Storage::disk('s3')->temporaryUrl($export->file_path, now()->addHours(config('analytics.export_ttl_hours')));
        }
        // local signed route handled in controller via signed middleware; fallback to stream
        return response()->download(storage_path('app/' . $export->file_path));
    }

    public function retryExport(AnalyticsExport $export)
    {
        // TODO: implement retry logic
    }
}
