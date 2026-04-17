<?php

namespace App\Services\Analytics;

use App\Models\AnalyticsAggregate;

class AnalyticsReadService
{
    /**
     * Build a query against analytics_aggregates for the requested params.
     * This is a small helper used across controllers and jobs.
     */
    public function buildAggregateQuery(array $params)
    {
        $q = AnalyticsAggregate::query();

        if (!empty($params['scope_context']) && is_array($params['scope_context'])) {
            $sc = $params['scope_context'];
            if (isset($sc['tenant_id'])) {
                $q->where('tenant_id', $sc['tenant_id']);
            }
        }

        if (!empty($params['filters']) && is_array($params['filters'])) {
            // map filters to where clauses (placeholder)
        }

        return $q;
    }
}
<?php

namespace App\Services\Analytics;

class AnalyticsReadService
{
    public function getSummary(array $filters = [])
    {
        // TODO: implement use of analytics_aggregates and caching
        return ['kpi' => [], 'charts' => [], 'table' => ['rows'=>[]]];
    }

    public function getAspekAggregates(array $filters = [])
    {
        // TODO: implement
        return [];
    }

    public function getIndikatorAggregates(array $filters = [])
    {
        // TODO: implement
        return [];
    }
}
