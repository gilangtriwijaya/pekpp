<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\F01PenilaianController;
use App\Http\Controllers\F02ValidasiController;
use App\Http\Controllers\Api\V1\AnalyticsExportController;
use App\Http\Controllers\Api\StatistikPublikController;


/**
 * F01 Penilaian API Routes
 * Base path: /api/f01/
 */
Route::prefix('f01')->middleware(['auth:sanctum'])->group(function () {
    /**
     * GET /api/f01/pengisian
     * Create or get draft pengisian for periode and upp
     */
    Route::post('/pengisian', [F01PenilaianController::class, 'getPengisian']);

    /**
     * POST /api/f01/submit
     * Submit all answers - bulk save to database
     */
    Route::post('/submit', [F01PenilaianController::class, 'submit']);

    /**
     * POST /api/f01/validate
     * Validate answers per aspek
     */
    Route::post('/validate', [F01PenilaianController::class, 'validateAspek']);

    /**
     * GET /api/f01/{pengisianId}
     * Get specific pengisian with all answers
     */
    Route::get('/{pengisianId}', [F01PenilaianController::class, 'show'])->name('f01.api.show');
});

Route::prefix('publik')
    ->middleware(['throttle:publik', 'api.publik.key'])
    ->group(function () {
        Route::get('/statistik', [StatistikPublikController::class, 'show']);
        Route::get('/health', [StatistikPublikController::class, 'health']);
    });

/**
 * F02 Validasi Dashboard API Routes
 * Base path: /api/f02/dashboard/
 */
Route::prefix('f02/dashboard')->middleware(['auth:sanctum'])->group(function () {
    /**
     * GET /api/f02/dashboard/overview
     * Get dashboard overview statistics
     */
    Route::get('/overview', [F02ValidasiController::class, 'dashboardOverview'])->name('f02.dashboard.overview');

    /**
     * GET /api/f02/dashboard/validasi-progress
     * Get validasi progress data for chart
     */
    Route::get('/validasi-progress', [F02ValidasiController::class, 'dashboardValidasiProgress'])->name('f02.dashboard.validasi-progress');

    /**
     * GET /api/f02/dashboard/aspek-comparison
     * Get aspek comparison data
     */
    Route::get('/aspek-comparison', [F02ValidasiController::class, 'dashboardAspekComparison'])->name('f02.dashboard.aspek-comparison');
});

// Analytics API (versioned)
Route::prefix('analytics')->middleware(['auth:sanctum'])->group(function () {
    // POST /api/analytics/exports -> create export (CSV/PDF)
    Route::post('/exports', [AnalyticsExportController::class, 'store'])->name('api.analytics.exports.store');
    // GET /api/analytics/exports/{id}/status
    Route::get('/exports/{id}/status', [AnalyticsExportController::class, 'status'])->name('api.analytics.exports.status');
    // GET /api/analytics/exports/{id}/download
    Route::get('/exports/{id}/download', [AnalyticsExportController::class, 'download'])->name('api.analytics.exports.download');
    // POST /api/analytics/exports/{id}/retry
    Route::post('/exports/{id}/retry', [AnalyticsExportController::class, 'retry'])->name('api.analytics.exports.retry')->middleware('can:download,App\\Models\\AnalyticsExport');
});
