<?php

namespace App\Services\Analytics;

use Illuminate\Http\Request;

final class ScopeContext
{
    public function __construct(
        public readonly ?int $tenantId,
        public readonly ?string $scopeKey,
        public readonly ?int $userId,
        public readonly array $roles = [],
        public readonly ?string $correlationId = null
    ) {}

    public static function fromRequest(Request $r): self
    {
        $user = $r->user();
        $tenantId = $r->input('scope_context.tenant_id') ?? $user?->tenant_id ?? null;
        $scopeKey = $r->input('scope_context.scope_key') ?? null;
        $roles = [];
        if ($user && method_exists($user, 'getRoleNames')) {
            $roles = $user->getRoleNames()->toArray();
        }
        $correlation = $r->input('correlation_id') ?? (string) (now()->timestamp . rand(1000,9999));

        return new self($tenantId, $scopeKey, $user?->id ?? null, $roles, $correlation);
    }

    public function toArray(): array
    {
        return [
            'tenant_id' => $this->tenantId,
            'scope_key' => $this->scopeKey,
            'user_id' => $this->userId,
            'roles' => $this->roles,
            'correlation_id' => $this->correlationId,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['tenant_id'] ?? null,
            $data['scope_key'] ?? null,
            $data['user_id'] ?? null,
            $data['roles'] ?? [],
            $data['correlation_id'] ?? null,
        );
    }
}
