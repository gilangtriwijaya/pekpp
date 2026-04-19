<?php

namespace App\Http\Controllers;

use App\Models\AnalyticsExport;
use App\Services\Analytics\AnalyticsExportOrchestrator;
use Illuminate\Http\Request;

class AnalyticsExportController extends Controller
{
    protected $orchestrator;

    public function __construct(AnalyticsExportOrchestrator $orchestrator)
    {
        $this->middleware('role:admin|analyst');
        $this->orchestrator = $orchestrator;
    }

    public function exportCsv(Request $request)
    {
        $params = $request->all();
        return $this->orchestrator->handleCsvRequest($request->user(), $params);
    }

    public function enqueuePdf(Request $request)
    {
        $params = $request->all();
        return $this->orchestrator->handlePdfRequest($request->user(), $params);
    }

    public function status(AnalyticsExport $export)
    {
        $this->authorize('view', $export);
        return response()->json($export->only(['id','status','file_path','error_message','processed_rows','total_rows_estimate','progress_percent']));
    }

    public function download(AnalyticsExport $export)
    {
        $this->authorize('download', $export);
        // signed route or disk temporaryUrl handled in orchestrator
        return app(AnalyticsExportOrchestrator::class)->downloadExport($export);
    }

    public function retry(AnalyticsExport $export)
    {
        $this->authorize('retry', $export);
        app(AnalyticsExportOrchestrator::class)->retryExport($export);
        return response()->json(['status' => 'retrying']);
    }
}
