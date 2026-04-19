<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\Analytics\ScopeContext;

class ScopeContextTest extends TestCase
{
    public function test_to_from_array_roundtrip()
    {
        $data = [
            'tenant_id' => 1,
            'scope_key' => 'opd:1',
            'user_id' => 42,
            'roles' => ['analyst'],
            'correlation_id' => 'abc-123',
        ];

        $sc = ScopeContext::fromArray($data);
        $this->assertInstanceOf(ScopeContext::class, $sc);
        $this->assertEquals($data['tenant_id'], $sc->tenantId);
        $this->assertEquals($data['scope_key'], $sc->scopeKey);
        $this->assertEquals($data['user_id'], $sc->userId);
        $this->assertEquals($data['roles'], $sc->roles);
        $this->assertEquals($data['correlation_id'], $sc->correlationId);

        $arr = $sc->toArray();
        $this->assertEquals($data['tenant_id'], $arr['tenant_id']);
    }
}
