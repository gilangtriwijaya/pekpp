<?php

namespace App\Services\Analytics;

use App\Models\AnalyticsAggregate;
use App\Models\AnalyticsExport;

class AnalyticsExportService
{
    /**
     * Estimate number of rows the export will produce using analytics_aggregates.
     * This method is intentionally conservative and uses DB COUNT() on aggregates.
     */
    public function estimateRows(array $params): int
    {
        $query = AnalyticsAggregate::query()
            ->when(isset($params['scope_context']) && is_array($params['scope_context']), function ($q) use ($params) {
                $sc = $params['scope_context'];
                if (isset($sc['tenant_id'])) {
                    $q->where('tenant_id', $sc['tenant_id']);
                }
                return $q;
            })
            ->when(isset($params['filters']) && is_array($params['filters']), function ($q) use ($params) {
                // Map allowed filters to aggregate columns here.
                // Placeholder: operators & mapping to be implemented per project needs.
                return $q;
            })
            ->where('level', 'indicator');

        return (int) $query->toBase()->count();
    }

    /**
     * Placeholder: create export record. Implementation will validate idempotency,
     * persist params and enqueue the job.
     */
    public function createExportRecord(array $data): AnalyticsExport
    {
        return AnalyticsExport::create($data);
    }
}
