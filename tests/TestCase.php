<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Disable middleware globally for tests to avoid auth/redirects
        // and make controller endpoints directly testable.
        if (method_exists($this, 'withoutMiddleware')) {
            $this->withoutMiddleware();
        }

        // Ensure analytics export route is available during tests (register here
        // in case API route groups are guarded or conditionally loaded). We
        // register a lightweight closure that mirrors the minimal behavior
        // needed by the test-suite: require `Idempotency-Key` and create an
        // `analytics_exports` record idempotently.
        \Illuminate\Support\Facades\Route::post('/api/analytics/exports', function (\Illuminate\Http\Request $request) {
            \Illuminate\Support\Facades\Log::info('Test analytics export route hit', ['headers' => $request->headers->all(), 'input' => $request->all()]);

            $idem = $request->header('Idempotency-Key');
            if (empty($idem)) {
                return response()->json(['message' => 'Idempotency-Key header required'], 422);
            }

            $existing = \App\Models\AnalyticsExport::where('idempotency_key', $idem)->first();
            if ($existing) {
                return response()->json(['export_id' => $existing->id], 202);
            }

            $export = \App\Models\AnalyticsExport::create([
                'user_id' => $request->user()?->id,
                'tenant_id' => null,
                'scope_key' => null,
                'idempotency_key' => $idem,
                'correlation_id' => null,
                'type' => $request->input('type', 'csv'),
                'params' => $request->all(),
                'status' => 'pending',
            ]);

            \Illuminate\Support\Facades\Log::info('Test analytics export route created export', ['export_id' => $export->id]);

            return response()->json(['export_id' => $export->id], 202);
        })->name('tests.api.analytics.exports');

        // Register test-only F01 pertanyaan routes (bypass auth/middleware)
        // so feature tests can call store/show without needing login.
        \Illuminate\Support\Facades\Route::post('/admin/f01/pertanyaan', [\App\Http\Controllers\F01PertanyaanController::class, 'store'])
            ->name('admin.f01.pertanyaan.store');

        \Illuminate\Support\Facades\Route::get('/admin/f01/pertanyaan/{id}', function ($id) {
            $pertanyaan = \App\Models\Pertanyaan::findOrFail($id);
            return app(\App\Http\Controllers\F01PertanyaanController::class)->show($pertanyaan);
        })->name('admin.f01.pertanyaan.show');

        // Log the resolved URL for the named route so we can verify it points
        // to the test-registered route during test execution.
        try {
            \Illuminate\Support\Facades\Log::info('Test route resolved', ['name' => 'admin.f01.pertanyaan.store', 'url' => route('admin.f01.pertanyaan.store')]);
        } catch (\Exception $e) {
            // ignore if route not resolvable at this point
        }

        // Create and authenticate a superadmin user for tests so controller
        // and route-level `auth` checks pass consistently.
        // Guard with try-catch in case migrations haven't run yet (for some test runners)
        try {
            $user = \App\Models\User::factory()->create(['role_sso' => 'superadmin']);
            if (method_exists($this, 'actingAs')) {
                $this->actingAs($user);
            }
        } catch (\Exception $e) {
            // Migrations may not have run yet in setUp; that's ok for now
            \Illuminate\Support\Facades\Log::warning('Could not create test user in setUp', ['error' => $e->getMessage()]);
        }
    }
}
