<?php

namespace App\Http\Controllers;

use App\Services\Analytics\AnalyticsReadService;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    protected $readService;

    public function __construct(AnalyticsReadService $readService)
    {
        $this->middleware('role:admin|analyst');
        $this->readService = $readService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['periode_id','provinsi_id','opd_id','upp_id','aspek_id','indikator_id','validated','page','per_page']);
        $summary = $this->readService->getSummary($filters);
        return view('analytics.index', compact('summary','filters'));
    }

    public function aspek(Request $request)
    {
        $filters = $request->only(['periode_id','upp_id']);
        return response()->json($this->readService->getAspekAggregates($filters));
    }

    public function indikator(Request $request)
    {
        $filters = $request->only(['periode_id','upp_id','aspek_id']);
        return response()->json($this->readService->getIndikatorAggregates($filters));
    }
}
