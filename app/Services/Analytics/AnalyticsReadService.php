<?php

namespace App\Services\Analytics;

use App\Models\AnalyticsAggregate;

class AnalyticsReadService
{
    /**
     * Build a query against analytics_aggregates for the requested params.
     */
    public function buildAggregateQuery(array $params = [])
    {
        $q = AnalyticsAggregate::query();

        if (!empty($params['scope_context']) && is_array($params['scope_context'])) {
            $sc = $params['scope_context'];
            if (isset($sc['tenant_id'])) {
                $q->where('tenant_id', $sc['tenant_id']);
            }
            if (!empty($sc['scope_key'])) {
                $q->where('scope_key', $sc['scope_key']);
            }
        }

        if (!empty($params['filters']) && is_array($params['filters'])) {
            // map filters to where clauses (periode_id, upp_id, aspek_id, indikator_id, etc.)
            if (!empty($params['filters']['periode_id'])) {
                $q->where('periode_id', $params['filters']['periode_id']);
            }
            if (!empty($params['filters']['upp_id'])) {
                $q->where('upp_id', $params['filters']['upp_id']);
            }
            if (!empty($params['filters']['aspek_id'])) {
                $q->where('aspek_id', $params['filters']['aspek_id']);
            }
            if (!empty($params['filters']['indikator_id'])) {
                $q->where('indikator_id', $params['filters']['indikator_id']);
            }
        }

        return $q;
    }

    public function getSummary(array $filters = [])
    {
        // Minimal placeholder using aggregates; real implementation will populate KPIs and charts.
        $query = $this->buildAggregateQuery($filters)->where('level', 'indicator');
        $total = $query->toBase()->count();

        return ['kpi' => ['total_rows' => $total], 'charts' => [], 'table' => ['rows' => [], 'meta' => []]];
    }

    public function getAspekAggregates(array $filters = [])
    {
        $query = AnalyticsAggregate::query()
            ->where('level', 'aspek')
            ->select('aspek_id', 'aspek_nama', 'skor_aspek')
            ->groupBy('aspek_id', 'aspek_nama');

        if (!empty($filters['periode_id'])) {
            $query->where('periode_id', $filters['periode_id']);
        }
        if (!empty($filters['upp_id'])) {
            $query->where('upp_id', $filters['upp_id']);
        }

        return $query->get();
    }

    public function getIndikatorAggregates(array $filters = [])
    {
        $query = AnalyticsAggregate::query()
            ->where('level', 'indikator')
            ->select('indikator_id', 'indikator_nama', 'skor_indikator', 'aspek_id')
            ->groupBy('indikator_id', 'indikator_nama', 'aspek_id');

        if (!empty($filters['periode_id'])) {
            $query->where('periode_id', $filters['periode_id']);
        }
        if (!empty($filters['upp_id'])) {
            $query->where('upp_id', $filters['upp_id']);
        }
        if (!empty($filters['aspek_id'])) {
            $query->where('aspek_id', $filters['aspek_id']);
        }

        return $query->get();
    }
}
