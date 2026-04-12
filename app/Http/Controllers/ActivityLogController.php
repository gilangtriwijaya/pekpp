<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $q = (string) $request->query('q', '');
        $userFilter = $request->query('user');
        $actionFilter = (string) $request->query('action', '');
        $start = $request->query('start');
        $end = $request->query('end');

        $query = ActivityLog::query()->orderBy('created_at', 'desc');
        if ($q !== '') {
            $query->where(function($sub) use ($q) {
                $sub->where('action', 'like', "%{$q}%")
                    ->orWhere('route', 'like', "%{$q}%")
                    ->orWhere('path', 'like', "%{$q}%")
                    ->orWhere('ip', 'like', "%{$q}%");
            });
        }

        if ($userFilter) {
            $query->where('user_id', $userFilter);
        }

        if ($actionFilter !== '') {
            $query->where('action', 'like', "%{$actionFilter}%");
        }

        // Normalize and validate date range: if start > end, swap them
        if ($start) {
            $startTs = strtotime($start);
        } else {
            $startTs = null;
        }
        if ($end) {
            $endTs = strtotime($end);
        } else {
            $endTs = null;
        }

        if ($startTs && $endTs && $startTs > $endTs) {
            // swap
            [$startTs, $endTs] = [$endTs, $startTs];
            $start = date('Y-m-d', $startTs);
            $end = date('Y-m-d', $endTs);
        }

        if ($start && $end) {
            $from = $start . ' 00:00:00';
            $to = $end . ' 23:59:59';
            $query->whereBetween('created_at', [$from, $to]);
        } elseif ($start) {
            $query->where('created_at', '>=', $start . ' 00:00:00');
        } elseif ($end) {
            $query->where('created_at', '<=', $end . ' 23:59:59');
        }

        $logs = $query->paginate(25)->withQueryString();

        // Limit users dropdown to those who exist in activity_logs to keep list relevant
        $userIds = ActivityLog::query()->whereNotNull('user_id')->distinct()->pluck('user_id')->filter()->values();
        $users = User::whereIn('id', $userIds)->orderBy('nama')->get();

        return view('activity_logs.index', compact('logs','q','users','userFilter','actionFilter','start','end'));
    }

    public function show(Request $request, $id)
    {
        $log = ActivityLog::with('user')->findOrFail($id);

        $payload = [
            'id' => $log->id,
            'created_at' => $log->created_at->format('Y-m-d H:i:s') . ' WIB',
            'user' => $log->user ? $log->user->nama : ($log->user_id ? 'User#'.$log->user_id : null),
            'user_id' => $log->user_id,
            'action' => $log->action,
            'route' => $log->route,
            'method' => $log->method,
            'path' => $log->path,
            'ip' => $log->ip,
            'user_agent' => $log->user_agent,
            'params' => $log->params,
        ];

        try {
            \Illuminate\Support\Facades\Log::debug('ActivityLogController@show payload', ['id' => $id, 'payload' => $payload, 'is_ajax' => $request->ajax(), 'headers' => $request->headers->all()]);
        } catch (\Throwable $e) {
            // ignore logging errors
        }

        return response()->json($payload);
    }
}
