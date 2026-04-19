<?php

namespace App\Http\Controllers;

use App\Models\AnalyticsReportSchedule;
use Illuminate\Http\Request;

class AnalyticsReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin|analyst');
    }

    public function schedule(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'frequency' => 'required|in:daily,weekly,monthly',
            'params' => 'nullable|array',
        ]);

        $sched = AnalyticsReportSchedule::create([
            'name' => $data['name'],
            'user_id' => $request->user()->id,
            'frequency' => $data['frequency'],
            'params' => $data['params'] ?? null,
        ]);

        return response()->json($sched, 201);
    }
}
