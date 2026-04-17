<?php

namespace App\Services\Analytics;

use App\Models\AnalyticsAggregate;
use App\Models\AnalyticsExport;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

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

    public function findExistingByIdempotency(?string $idempotencyKey, ?int $tenantId = null)
    {
        if (empty($idempotencyKey)) {
            return null;
        }

        $q = AnalyticsExport::query()->where('idempotency_key', $idempotencyKey);
        if ($tenantId) {
            $q->where('tenant_id', $tenantId);
        }
        return $q->first();
    }

    public function checkRateLimits(int $userId = null, int $tenantId = null, array $roles = []): void
    {
        $cfg = config('analytics.rate_limits', []);
        $bypass = $cfg['bypass_roles'] ?? [];
        if (!empty(array_intersect($roles, $bypass))) {
            return; // bypass
        }

        $date = now()->format('Y-m-d');
        if ($userId) {
            $userKey = "analytics:exports:rate:user:{$userId}:{$date}";
            $userCount = Redis::incr($userKey);
            if ($userCount === 1) {
                Redis::expireAt($userKey, now()->endOfDay()->getTimestamp());
            }
            $limit = (int) ($cfg['per_user_per_day'] ?? 5);
            if ($userCount > $limit) {
                throw new \RuntimeException('user_rate_limit_exceeded');
            }
        }

        if ($tenantId) {
            $tenantKey = "analytics:exports:rate:tenant:{$tenantId}:{$date}";
            $tenantCount = Redis::incr($tenantKey);
            if ($tenantCount === 1) {
                Redis::expireAt($tenantKey, now()->endOfDay()->getTimestamp());
            }
            $limitT = (int) ($cfg['per_tenant_per_day'] ?? 100);
            if ($tenantCount > $limitT) {
                throw new \RuntimeException('tenant_rate_limit_exceeded');
            }
        }
    }

    /**
     * Create export record respecting idempotency rules.
     * Returns existing record when applicable.
     * Throws RuntimeException when idempotency indicates failure and client must call retry.
     */
    public function createExportRecord(array $data): AnalyticsExport
    {
        $idempotency = $data['idempotency_key'] ?? null;
        $tenantId = $data['tenant_id'] ?? null;

        if ($idempotency) {
            $existing = $this->findExistingByIdempotency($idempotency, $tenantId);
            if ($existing) {
                // If failed, signal conflict
                if (in_array($existing->status, ['failed'])) {
                    throw new \RuntimeException('idempotency_existing_failed');
                }
                // Return existing record for pending/processing/ready
                return $existing;
            }
        }

        // Persist lightweight record
        $export = AnalyticsExport::create([
            'user_id' => $data['user_id'] ?? null,
            'tenant_id' => $tenantId,
            'scope_key' => $data['scope_key'] ?? null,
            'idempotency_key' => $idempotency,
            'correlation_id' => $data['correlation_id'] ?? Str::uuid()->toString(),
            'type' => $data['type'] ?? 'csv',
            'params' => $data['params'] ?? null,
            'status' => $data['status'] ?? 'pending',
            'total_rows_estimate' => $data['total_rows_estimate'] ?? null,
        ]);

        return $export;
    }
}
