<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Services\Analytics\AnalyticsReadService;
use App\Services\Analytics\AnalyticsExportService;
use App\Models\AnalyticsExport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Jobs\Analytics\GenerateAnalyticsCsvJob;
use App\Jobs\Analytics\GenerateAnalyticsPdfJob;

class AnalyticsPanel extends Component
{
    use WithPagination;

    public $periode_id;
    public $tenant_id;
    public $perPage = 15;
    public $exportStatus = null;

    public $topLabels = [];
    public $topData = [];

    protected $listeners = ['refreshExportStatus' => 'refreshExportStatus'];

    public function mount()
    {
        $this->perPage = 15;
    }

    public function updatedPeriodeId()
    {
        $this->resetPage();
    }

    public function updatedTenantId()
    {
        $this->resetPage();
    }

    public function render(AnalyticsReadService $readService)
    {
        $filters = [
            'scope_context' => ['tenant_id' => $this->tenant_id],
            'filters' => ['periode_id' => $this->periode_id],
        ];

        $rows = $readService->buildAggregateQuery($filters)
            ->where('level', 'indicator')
            ->orderByDesc('avg_score')
            ->paginate($this->perPage);

        $this->loadChartData($filters);

        return view('livewire.analytics.panel', ['rows' => $rows]);
    }

    protected function loadChartData(array $filters)
    {
        $q = \App\Models\AnalyticsAggregate::query()
            ->selectRaw('upp_id, AVG(avg_score) as avg_score')
            ->when(!empty($filters['scope_context']['tenant_id']), function ($q) use ($filters) {
                $q->where('tenant_id', $filters['scope_context']['tenant_id']);
            })
            ->when(!empty($filters['filters']['periode_id']), function ($q) use ($filters) {
                $q->where('periode_id', $filters['filters']['periode_id']);
            })
            ->groupBy('upp_id')
            ->orderByDesc('avg_score')
            ->limit(5);

        $results = $q->get();
        $this->topLabels = $results->map(fn($r) => 'UPP '.$r->upp_id)->toArray();
        $this->topData = $results->map(fn($r) => round((float)$r->avg_score, 2))->toArray();
    }

    public function createExport($type = 'csv')
    {
        $user = Auth::user();
        $roles = [];
        if ($user && method_exists($user, 'getRoleNames')) {
            $roles = $user->getRoleNames()->toArray();
        }

        $service = app(AnalyticsExportService::class);

        try {
            $service->checkRateLimits($user?->id, $this->tenant_id, $roles);
        } catch (\RuntimeException $e) {
            $this->dispatchBrowserEvent('show-toast', ['type' => 'error', 'message' => 'Rate limit exceeded']);
            return;
        }

        $idempotency = uniqid('live_', true);

        try {
            $export = $service->createExportRecord([
                'user_id' => $user?->id,
                'tenant_id' => $this->tenant_id,
                'scope_key' => null,
                'idempotency_key' => $idempotency,
                'correlation_id' => null,
                'type' => $type,
                'params' => [
                    'scope_context' => ['tenant_id' => $this->tenant_id, 'scope_key' => null],
                    'filters' => ['periode_id' => $this->periode_id],
                ],
                'status' => 'pending',
                'total_rows_estimate' => $service->estimateRows(['scope_context' => ['tenant_id' => $this->tenant_id], 'filters' => ['periode_id' => $this->periode_id]]),
            ]);
        } catch (\RuntimeException $e) {
            $this->dispatchBrowserEvent('show-toast', ['type' => 'error', 'message' => 'Idempotency conflict: '.$e->getMessage()]);
            return;
        }

        $this->exportStatus = ['id' => $export->id, 'status' => $export->status];

        if ($type === 'csv') {
            GenerateAnalyticsCsvJob::dispatch($export->id);
        } else {
            GenerateAnalyticsPdfJob::dispatch($export->id);
        }

        $this->dispatchBrowserEvent('show-toast', ['type' => 'success', 'message' => 'Export queued']);
    }

    public function refreshExportStatus()
    {
        if (empty($this->exportStatus['id'])) {
            return;
        }

        $export = AnalyticsExport::find($this->exportStatus['id']);
        if (! $export) return;

        $this->exportStatus = [
            'id' => $export->id,
            'status' => $export->status,
            'file_path' => $export->file_path,
            'finished_at' => $export->finished_at,
        ];

        if ($export->status === 'ready') {
            $this->dispatchBrowserEvent('export-ready', ['id' => $export->id]);
        }
    }

    public function downloadExport(int $id)
    {
        $export = AnalyticsExport::find($id);
        if (! $export) {
            $this->dispatchBrowserEvent('show-toast', ['type' => 'error', 'message' => 'Export not found']);
            return;
        }

        $disk = config('analytics.storage_disk', 'local');
        if ($disk === 's3' && $export->file_path) {
            $url = Storage::disk('s3')->temporaryUrl($export->file_path, now()->addHours(config('analytics.export_ttl_hours', 48)));
            $this->dispatchBrowserEvent('open-export-url', ['url' => $url]);
            return;
        }

        $url = route('api.analytics.exports.download', ['id' => $id]);
        $this->dispatchBrowserEvent('open-export-url', ['url' => $url]);
    }
}
