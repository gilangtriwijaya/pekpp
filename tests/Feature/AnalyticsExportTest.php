<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AnalyticsExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_requires_idempotency_key()
    {
        $resp = $this->postJson('/api/analytics/exports', []);
        $resp->assertStatus(422);
    }

    // Further tests (queueing, streaming, retry) to be implemented
}
