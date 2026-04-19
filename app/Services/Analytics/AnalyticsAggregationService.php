<?php

namespace App\Services\Analytics;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AnalyticsAggregationService
{
    /**
     * Canonicalize dimension parts into sha1 using NUL separator as recommended.
     */
    public function canonicalizeDimension(array $parts): string
    {
        // ensure deterministic order
        $ordered = [];
        foreach (['level','periode','scope','upp','aspek','indikator'] as $k) {
            $ordered[$k] = isset($parts[$k]) ? (string)$parts[$k] : '';
        }

        $canon = implode("\0", array_map(function($k, $v){ return "{$k}:{$v}"; }, array_keys($ordered), $ordered));
        return sha1($canon);
    }

    /**
     * Rebuild indicator-level aggregates for a periode. This implementation is conservative
     * and computes total_responses and avg_score. Median and pct_validated are left as
     * placeholders to be improved (SQL window or PHP median when data small).
     *
     * @param int $periodeId
     * @param array|null $scopeContext
     * @return int number of aggregate rows upserted
     */
    public function rebuildPeriod(int $periodeId, ?array $scopeContext = null): int
    {
        $scopeKey = $scopeContext['scope_key'] ?? null;

        // base query: join F02 indikator values -> F02 validasi -> F01 pengisian -> indikator
        $base = DB::table('f02_indikator_validasi as iv')
            ->join('f02_validasi as v', 'iv.f02_validasi_id', 'v.id')
            ->join('f01_pengisian as f', 'v.f01_pengisian_id', 'f.id')
            ->join('indikator as i', 'iv.indikator_id', 'i.id')
            ->where('f.periode_id', $periodeId)
            ->where('f.is_latest_version', true);

        // apply tenant/scope filtering if provided
        if (!empty($scopeContext['tenant_id'])) {
            // best-effort: if f01_pengisian has tenant/upp relation, try filtering by upp->tenant
            // here we assume 'f' has tenant_id if present
            $base->where('f.tenant_id', $scopeContext['tenant_id']);
        }

        $groups = $base->selectRaw('f.periode_id as periode_id, f.upp_id as upp_id, i.aspek_id as aspek_id, iv.indikator_id as indikator_id, COUNT(*) as total_responses, AVG(iv.nilai) as avg_score')
            ->groupBy('f.periode_id', 'f.upp_id', 'i.aspek_id', 'iv.indikator_id')
            ->get();

        $upserted = 0;
        foreach ($groups as $g) {
            $parts = [
                'level' => 'indicator',
                'periode' => (string)$g->periode_id,
                'scope' => (string)($scopeKey ?? ''),
                'upp' => (string)($g->upp_id ?? ''),
                'aspek' => (string)($g->aspek_id ?? ''),
                'indikator' => (string)($g->indikator_id ?? ''),
            ];

            $hash = $this->canonicalizeDimension($parts);

            $data = [
                'periode_id' => $g->periode_id,
                'tenant_id' => $scopeContext['tenant_id'] ?? null,
                'scope_key' => $scopeKey,
                'level' => 'indicator',
                'dimension_hash' => $hash,
                'upp_id' => $g->upp_id,
                'aspek_id' => $g->aspek_id,
                'indikator_id' => $g->indikator_id,
                'total_responses' => (int)$g->total_responses,
                'avg_score' => round((float)$g->avg_score, 2),
                'median_score' => round((float)$g->avg_score, 2), // fallback: use avg as placeholder
                'pct_validated' => 0.00,
                'pct_empty' => 0.00,
                'computed_at' => now(),
                'aggregate_version' => 1,
            ];

            // Upsert by unique key (periode_id, scope_key, dimension_hash)
            DB::table('analytics_aggregates')->updateOrInsert(
                ['periode_id' => $g->periode_id, 'scope_key' => $scopeKey, 'dimension_hash' => $hash],
                $data
            );

            $upserted++;
        }

        return $upserted;
    }
}
