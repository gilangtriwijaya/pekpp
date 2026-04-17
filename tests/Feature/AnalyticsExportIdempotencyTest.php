<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\AnalyticsExport;

class AnalyticsExportIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_idempotency_returns_existing_record()
    {
        // create user
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum');

        $headers = ['Idempotency-Key' => 'idem-test-1'];

        $payload = ['type' => 'csv', 'scope_context' => ['tenant_id' => null], 'filters' => []];

        $resp1 = $this->withHeaders($headers)->postJson('/api/analytics/exports', $payload);
        $resp1->assertStatus(202);
        $data1 = $resp1->json();
        $this->assertArrayHasKey('export_id', $data1);

        // second request with same idempotency key should return same export id
        $resp2 = $this->withHeaders($headers)->postJson('/api/analytics/exports', $payload);
        $resp2->assertStatus(202);
        $data2 = $resp2->json();
        $this->assertEquals($data1['export_id'], $data2['export_id']);

        // ensure record exists in DB
        $this->assertDatabaseHas('analytics_exports', ['id' => $data1['export_id'], 'idempotency_key' => 'idem-test-1']);
    }
}
