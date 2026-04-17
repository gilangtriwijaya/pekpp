<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsReportSchedule;
use Illuminate\Http\Request;

class AnalyticsReportController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|string', 'frequency' => 'required|string', 'params' => 'nullable|array']);
        $schedule = AnalyticsReportSchedule::create([
            'name' => $data['name'],
            'user_id' => $request->user()?->id,
            'frequency' => $data['frequency'],
            'params' => $data['params'] ?? null,
            'enabled' => true,
        ]);

        return response()->json(['schedule_id' => $schedule->id], 201);
    }
}
