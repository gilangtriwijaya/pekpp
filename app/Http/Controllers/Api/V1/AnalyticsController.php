<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Analytics\AnalyticsReadService;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function index(Request $request, AnalyticsReadService $read)
    {
        // Placeholder: return a summary + charts + empty table meta
        $params = $request->all();
        $query = $read->buildAggregateQuery($params);

        return response()->json([
            'summary' => [],
            'charts' => [],
            'table' => ['rows' => [], 'meta' => ['total' => (int) $query->count()]],
        ]);
    }
}
