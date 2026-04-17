<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsExport;
use App\Services\Analytics\AnalyticsExportService;
use Illuminate\Http\Request;

class AnalyticsExportController extends Controller
{
    public function store(Request $request, AnalyticsExportService $service)
    {
        $idempotency = $request->header('Idempotency-Key');
        if (empty($idempotency)) {
            return response()->json(['message' => 'Idempotency-Key header required'], 422);
        }

        $params = $request->all();
        $estimate = $service->estimateRows(['scope_context' => $params['scope_context'] ?? null, 'filters' => $params['filters'] ?? null]);

        $effectiveThreshold = (int) floor(config('analytics.sync_threshold', 50000) * 0.8);

        if ($estimate <= $effectiveThreshold) {
            return response()->json(['message' => 'sync_allowed', 'estimated_rows' => $estimate]);
        }

        // queued path (placeholder record)
        $export = AnalyticsExport::create([
            'user_id' => $request->user()?->id,
            'tenant_id' => $params['scope_context']['tenant_id'] ?? null,
            'scope_key' => $params['scope_context']['scope_key'] ?? null,
            'idempotency_key' => $idempotency,
            'correlation_id' => $params['correlation_id'] ?? null,
            'type' => 'csv',
            'params' => $params,
            'status' => 'pending',
            'total_rows_estimate' => $estimate,
        ]);

        return response()->json(['message' => 'queued', 'export_id' => $export->id], 202);
    }

    public function status($id)
    {
        $export = AnalyticsExport::findOrFail($id);
        return response()->json($export);
    }

    public function download($id)
    {
        // Placeholder: authorize + stream file
        $export = AnalyticsExport::findOrFail($id);
        return response()->json(['message' => 'download endpoint not implemented', 'export' => $export->id]);
    }
}
