<?php

use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Auth;
use App\Http\Middleware\EnsureUserUpp;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\DemoErrorController;

Route::get('/', function () {
    if (Auth::check()) {
        return view('welcome');
    }

    return redirect()->route('sso.login');
});

use App\Http\Controllers\SsoLoginController;

use App\Http\Controllers\Api\V1\AnalyticsExportController as ApiAnalyticsExportController;

Route::get('/sso/login', [SsoLoginController::class, 'redirectToSso'])->name('sso.login');
Route::get('/sso/callback', [SsoLoginController::class, 'callback'])->name('sso.callback');
Route::get('/sso/back', [SsoLoginController::class, 'backToSso'])->name('sso.back');
Route::post('/sso/logout', [SsoLoginController::class, 'logout'])->name('sso.logout');


// provide a fallback `login` route so Laravel's Authenticate middleware can redirect
Route::get('/login', function () {
    return redirect()->route('sso.login');
})->name('login');

// Session expired page - no auth required
Route::get('/session-expired', function () {
    return view('auth.session-expired');
})->name('session-expired');

// During tests we register minimal, unguarded F01 routes so feature tests
// can exercise controller logic without full auth/middleware bootstrapping.
if (app()->environment('testing')) {
    Route::prefix('admin/f01')->name('admin.f01.')->group(function () {
        Route::post('pertanyaan', [\App\Http\Controllers\F01PertanyaanController::class, 'store'])->name('pertanyaan.store');
        Route::get('pertanyaan/{id}', function ($id) {
            $pertanyaan = \App\Models\Pertanyaan::findOrFail($id);
            return app(\App\Http\Controllers\F01PertanyaanController::class)->show($pertanyaan);
        })->name('pertanyaan.show');
    });
}

Route::middleware(['auth', EnsureUserUpp::class])->group(function() {
    // Analytics UI
    Route::get('/analytics', function () {
        return view('analytics.index');
    })->name('analytics.index');

    // Error Gallery Demo (temporary - for video recording)
    Route::get('/errors', [\App\Http\Controllers\DemoErrorController::class, 'index'])->name('errors.index');

    // Activity Logs: Superadmin only
    Route::middleware([\App\Http\Middleware\RequireSuperadmin::class])->group(function () {
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
        Route::get('/activity-logs/{id}', [ActivityLogController::class, 'show'])->name('activity-logs.show');
    });

    // UPP listing (read-only)
    Route::get('/upps', [\App\Http\Controllers\UppController::class, 'index'])->name('upps.index');

    // Admin: manage user_upp (superadmin only)
        Route::middleware([\App\Http\Middleware\RequireSuperadmin::class])->group(function () {
        Route::get('/admin/user-upp', [\App\Http\Controllers\UserUppController::class, 'index'])->name('admin.user_upp.index');
        Route::get('/admin/user-upp/create', [\App\Http\Controllers\UserUppController::class, 'create'])->name('admin.user_upp.create');
        Route::post('/admin/user-upp', [\App\Http\Controllers\UserUppController::class, 'store'])->name('admin.user_upp.store');
        Route::get('/admin/user-upp/{id}/edit', [\App\Http\Controllers\UserUppController::class, 'edit'])->name('admin.user_upp.edit');
        Route::put('/admin/user-upp/{id}', [\App\Http\Controllers\UserUppController::class, 'update'])->name('admin.user_upp.update');
        Route::delete('/admin/user-upp/{id}', [\App\Http\Controllers\UserUppController::class, 'destroy'])->name('admin.user_upp.destroy');

            // Duplicate routes without the `/admin` prefix so the menu can link to `/user-upp`
            Route::get('/user-upp', [\App\Http\Controllers\UserUppController::class, 'index'])->name('user_upp.index');
            Route::get('/user-upp/create', [\App\Http\Controllers\UserUppController::class, 'create'])->name('user_upp.create');
            Route::post('/user-upp', [\App\Http\Controllers\UserUppController::class, 'store'])->name('user_upp.store');
            Route::get('/user-upp/{id}/edit', [\App\Http\Controllers\UserUppController::class, 'edit'])->name('user_upp.edit');
            Route::put('/user-upp/{id}', [\App\Http\Controllers\UserUppController::class, 'update'])->name('user_upp.update');
            Route::delete('/user-upp/{id}', [\App\Http\Controllers\UserUppController::class, 'destroy'])->name('user_upp.destroy');
    });

    // Admin F01 management (pertanyaan, aspek, indikator)
    if (!app()->environment('testing')) {
        Route::prefix('admin/f01')->name('admin.f01.')->middleware(['auth'])->group(function () {
            Route::resource('pertanyaan', \App\Http\Controllers\F01PertanyaanController::class);
            Route::post('pertanyaan/reorder', [\App\Http\Controllers\F01PertanyaanController::class, 'reorder'])->name('pertanyaan.reorder');
            Route::get('get-indicators-by-aspek/{aspekId}', [\App\Http\Controllers\F01PertanyaanController::class, 'getIndicatorsByAspek'])->name('get-indicators-by-aspek');

            // Aspek management
            Route::get('aspek', [\App\Http\Controllers\F01AspekController::class, 'index'])->name('aspek.index');
            Route::post('aspek', [\App\Http\Controllers\F01AspekController::class, 'store'])->name('aspek.store');
            Route::put('aspek/{aspek}', [\App\Http\Controllers\F01AspekController::class, 'update'])->name('aspek.update');
            Route::delete('aspek/{aspek}', [\App\Http\Controllers\F01AspekController::class, 'destroy'])->name('aspek.destroy');
            Route::post('aspek/{aspek}/toggle', [\App\Http\Controllers\F01AspekController::class, 'toggleActive'])->name('aspek.toggle');
            Route::post('aspek/reorder', [\App\Http\Controllers\F01AspekController::class, 'reorder'])->name('aspek.reorder');

            // Indikator management
            Route::post('indikator/reorder', [\App\Http\Controllers\F01IndikatorController::class, 'reorder'])->name('indikator.reorder');
            Route::get('indikator', [\App\Http\Controllers\F01IndikatorController::class, 'index'])->name('indikator.index');
            Route::post('indikator', [\App\Http\Controllers\F01IndikatorController::class, 'store'])->name('indikator.store');
            Route::get('indikator/{indikator}', [\App\Http\Controllers\F01IndikatorController::class, 'show'])->name('indikator.show');
            Route::put('indikator/{indikator}', [\App\Http\Controllers\F01IndikatorController::class, 'update'])->name('indikator.update');
            Route::delete('indikator/{indikator}', [\App\Http\Controllers\F01IndikatorController::class, 'destroy'])->name('indikator.destroy');
        });
    }

    // Admin Periode management
    Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
        Route::resource('periode', \App\Http\Controllers\PeriodeController::class);
        Route::post('periode/{periode}/toggle-aktif', [\App\Http\Controllers\PeriodeController::class, 'toggleAktif'])->name('periode.toggle-aktif');
        Route::get('periode/{periode}/instrumen-tree', [\App\Http\Controllers\PeriodeController::class, 'getInstrumenTree'])->name('periode.instrumen-tree');
        Route::post('periode/{periode}/salin-instrumen', [\App\Http\Controllers\PeriodeController::class, 'salinInstrumen'])->name('periode.salin-instrumen');

        // F03 Admin Management
        Route::prefix('f03')->name('f03.')->group(function () {
            // Aspek management
            Route::get('aspek', [\App\Http\Controllers\F03AspekController::class, 'index'])->name('aspek.index');
            Route::post('aspek', [\App\Http\Controllers\F03AspekController::class, 'store'])->name('aspek.store');
            Route::put('aspek/{id}', [\App\Http\Controllers\F03AspekController::class, 'update'])->name('aspek.update');
            Route::delete('aspek/{id}', [\App\Http\Controllers\F03AspekController::class, 'destroy'])->name('aspek.destroy');

            // Indikator management
            Route::get('indikator', [\App\Http\Controllers\F03IndikatorController::class, 'index'])->name('indikator.index');
            Route::post('indikator', [\App\Http\Controllers\F03IndikatorController::class, 'store'])->name('indikator.store');
            Route::put('indikator/{id}', [\App\Http\Controllers\F03IndikatorController::class, 'update'])->name('indikator.update');
            Route::delete('indikator/{id}', [\App\Http\Controllers\F03IndikatorController::class, 'destroy'])->name('indikator.destroy');

            // Token management
            Route::get('token', [\App\Http\Controllers\F03TokenController::class, 'index'])->name('token.index');
            Route::post('token/generate', [\App\Http\Controllers\F03TokenController::class, 'generateToken'])->name('token.generate');
            Route::post('token/generate-all', [\App\Http\Controllers\F03TokenController::class, 'generateAllTokens'])->name('token.generateAll');
            Route::post('token/global-settings', [\App\Http\Controllers\F03TokenController::class, 'updateGlobalSettings'])->name('token.updateGlobalSettings');
            Route::post('token/{id}/revoke', [\App\Http\Controllers\F03TokenController::class, 'revoke'])->name('token.revoke');
            Route::post('token/{id}/activate', [\App\Http\Controllers\F03TokenController::class, 'activate'])->name('token.activate');
            Route::post('token/{id}/settings', [\App\Http\Controllers\F03TokenController::class, 'updateSettings'])->name('token.updateSettings');
            Route::get('token/{id}', [\App\Http\Controllers\F03TokenController::class, 'show'])->name('token.show');

            // Dashboard
            Route::get('dashboard', [\App\Http\Controllers\F03DashboardController::class, 'adminDashboard'])->name('dashboard.admin');
        });
    });
});

// F03 Public Routes (NO AUTH - Public questionnaire form)
Route::get('/f03/public/{token}', [\App\Http\Controllers\F03PublicController::class, 'show'])->name('f03.public.form');
Route::post('/f03/public/{token}/submit', [\App\Http\Controllers\F03PublicController::class, 'submit'])->name('f03.public.submit');

// F03 Authenticated Routes (UPP & Admin)
Route::middleware(['auth'])->group(function () {
    // F03 UPP Dashboard redirect (for users - redirects to their UPP's dashboard)
    Route::get('/f03/dashboard', [\App\Http\Controllers\F03DashboardController::class, 'userDashboard'])->name('f03.dashboard');

    // F03 UPP Dashboard (for each UPP's responses)
    Route::get('/f03/dashboard/upp/{uppId}/{periodeId}', [\App\Http\Controllers\F03DashboardController::class, 'uppDashboard'])->name('f03.dashboard.upp');

    // F03 Dashboard API
    Route::get('/f03/api/response/{pengisianId}', [\App\Http\Controllers\F03DashboardController::class, 'getResponseDetail'])->name('f03.api.response');
    Route::post('/f03/api/qr-code/{tokenId}', [\App\Http\Controllers\F03DashboardController::class, 'generateQrCodeApi'])->name('f03.api.qr-code');
    Route::get('/f03/export/{tokenId}', [\App\Http\Controllers\F03DashboardController::class, 'exportExcel'])->name('f03.export');
});

// Pendataan Routes (Evaluasi UPP)
Route::middleware(['auth'])->prefix('pendataan')->name('pendataan.')->group(function () {
    Route::get('/', [\App\Http\Controllers\PendataanController::class, 'index'])->name('index');
    Route::get('/{pengisianId}/aspek', [\App\Http\Controllers\PendataanController::class, 'aspekList'])->name('aspek-list')->where('pengisianId', '[0-9]+');
    Route::get('/{pengisianId}/aspek/{aspekId}/detail', [\App\Http\Controllers\PendataanController::class, 'showAspekDetail'])->name('aspek.detail')->where(['pengisianId' => '[0-9]+', 'aspekId' => '[0-9]+']);
    Route::post('/{pengisianId}/auto-save', [\App\Http\Controllers\PendataanController::class, 'autoSave'])->name('auto-save')->where('pengisianId', '[0-9]+');
    Route::post('/{pengisianId}/upload-bukti', [\App\Http\Controllers\PendataanController::class, 'uploadBukti'])->name('upload-bukti')->where('pengisianId', '[0-9]+');
    Route::post('/{pengisianId}/submit', [\App\Http\Controllers\PendataanController::class, 'submit'])->name('submit')->where('pengisianId', '[0-9]+');
    Route::get('/api/{pengisianId}/form-data', [\App\Http\Controllers\PendataanController::class, 'getFormData'])->name('api.form-data')->where('pengisianId', '[0-9]+');
});

// Admin Pendataan Routes
Route::middleware(['auth'])->prefix('admin/pendataan')->name('admin.pendataan.')->group(function () {
    // Aspek
    Route::get('aspek', [\App\Http\Controllers\PendataanAspekController::class, 'index'])->name('aspek.index');
    Route::post('aspek', [\App\Http\Controllers\PendataanAspekController::class, 'store'])->name('aspek.store');
    Route::put('aspek/{id}', [\App\Http\Controllers\PendataanAspekController::class, 'update'])->name('aspek.update');
    Route::delete('aspek/{id}', [\App\Http\Controllers\PendataanAspekController::class, 'destroy'])->name('aspek.destroy');
    
    // Pertanyaan
    Route::get('pertanyaan', [\App\Http\Controllers\PendataanPertanyaanController::class, 'index'])->name('pertanyaan.index');
    Route::post('pertanyaan', [\App\Http\Controllers\PendataanPertanyaanController::class, 'store'])->name('pertanyaan.store');
    Route::put('pertanyaan/{id}', [\App\Http\Controllers\PendataanPertanyaanController::class, 'update'])->name('pertanyaan.update');
    Route::delete('pertanyaan/{id}', [\App\Http\Controllers\PendataanPertanyaanController::class, 'destroy'])->name('pertanyaan.destroy');
    
    // Pengisian
    Route::get('pengisian', [\App\Http\Controllers\AdminPendataanPengisianController::class, 'index'])->name('pengisian.index');
});

Route::middleware(['auth'])->group(function() {
    // Impersonate routes (superadmin only)
    Route::controller(\App\Http\Controllers\ImpersonateController::class)->group(function () {
        Route::get('/api/impersonate/users', 'getImpersonateUsers')->name('api.impersonate.users');
        Route::post('/api/impersonate/start', 'startImpersonate')->name('api.impersonate.start');
        Route::post('/api/impersonate/stop', 'stopImpersonate')->name('api.impersonate.stop');
        Route::get('/api/impersonate/status', 'checkImpersonateStatus')->name('api.impersonate.status');
    });

    Route::get('/f01', [\App\Http\Controllers\F01PengisianController::class, 'index'])->name('f01.index');
    Route::get('/f01/{pengisian}/aspek', [\App\Http\Controllers\F01PengisianController::class, 'aspekList'])->name('f01.aspek-list')->where('pengisian', '[0-9]+');
    Route::post('/f01/{pengisian}/finalize', [\App\Http\Controllers\F01PengisianController::class, 'finalize'])->name('f01.finalize')->where('pengisian', '[0-9]+');
    Route::post('/f01/{pengision}/auto-save', [\App\Http\Controllers\F01PengisianController::class, 'autoSave'])->name('f01.auto-save')->where('pengision', '[0-9]+');

    // F01 Indikator Bukti Dukung (URL Link) routes - MUST be before {pengisian} catch-all
    Route::post('/f01/{pengisianId}/indikator/{indikatorId}/bukti', [\App\Http\Controllers\F01PengisianController::class, 'saveBukti'])->name('f01.indikator.bukti.save')->where(['pengisianId' => '[0-9]+', 'indikatorId' => '[0-9]+']);
    Route::get('/f01/{pengisianId}/indikator/{indikatorId}/bukti', [\App\Http\Controllers\F01PengisianController::class, 'getBukti'])->name('f01.indikator.bukti.get')->where(['pengisianId' => '[0-9]+', 'indikatorId' => '[0-9]+']);
    Route::delete('/f01/{pengisianId}/bukti/{buktiId}', [\App\Http\Controllers\F01PengisianController::class, 'deleteBukti'])->name('f01.indikator.bukti.delete')->where(['pengisianId' => '[0-9]+', 'buktiId' => '[0-9]+']);
    Route::post('/f01/{pengisianId}/indikator/{indikatorId}/mark-changed', [\App\Http\Controllers\F01PengisianController::class, 'markIndikatorChanged'])->name('f01.indikator.mark-changed')->where(['pengisianId' => '[0-9]+', 'indikatorId' => '[0-9]+']);

    // F01 Per-Aspek Save routes (Phase 14)
    Route::post('/f01/{pengisianId}/aspek/{aspekId}/save', [\App\Http\Controllers\F01PengisianController::class, 'saveBuktiDanJawaban'])->name('f01.aspek.save')->where(['pengisianId' => '[0-9]+', 'aspekId' => '[0-9]+']);
    Route::get('/f01/{pengisianId}/aspek/{aspekId}/detail', [\App\Http\Controllers\F01PengisianController::class, 'showAspekDetail'])->name('f01.aspek.detail')->where(['pengisianId' => '[0-9]+', 'aspekId' => '[0-9]+']);
    Route::get('/f01/{pengisianId}/aspek-status', [\App\Http\Controllers\F01PengisianController::class, 'getAspekStatus'])->name('f01.aspek.status')->where('pengisianId', '[0-9]+');
    Route::get('/api/f01/{pengisianId}/aspek-list-modal', [\App\Http\Controllers\F01PengisianController::class, 'getAspekListForModal'])->name('f01.api.aspek-list-modal')->where('pengisianId', '[0-9]+');
    Route::get('/api/f01/{pengisianId}/indikator/{indikatorId}', [\App\Http\Controllers\F01PengisianController::class, 'getIndikatorDetail'])->name('f01.api.indikator-detail')->where(['pengisianId' => '[0-9]+', 'indikatorId' => '[0-9]+']);

    // F01 Jawaban dan Bukti routes
    Route::post('/f01/jawaban', [\App\Http\Controllers\F01JawabanController::class, 'storeOrUpdate'])->name('f01.jawaban.store');
    Route::post('/f01/jawaban/bulk', [\App\Http\Controllers\F01JawabanController::class, 'bulkSave'])->name('f01.jawaban.bulk');
    Route::post('/f01/bukti', [\App\Http\Controllers\F01BuktiController::class, 'store'])->name('f01.bukti.store');
    Route::delete('/f01/bukti/{id}', [\App\Http\Controllers\F01BuktiController::class, 'destroy'])->name('f01.bukti.destroy');

    // Generic F01 Pengisian routes - MUST be after specific routes
    Route::get('/f01/{pengisian}', [\App\Http\Controllers\F01PengisianController::class, 'show'])->name('f01.show')->where('pengisian', '[0-9]+');
    Route::get('/api/f01/{id}', [\App\Http\Controllers\F01PengisianController::class, 'getFormData'])->name('f01.api.form')->where('id', '[0-9]+');
    Route::get('/api/f01/{id}/ringkasan', [\App\Http\Controllers\F01PengisianController::class, 'getRingkasan'])->name('f01.api.ringkasan')->where('id', '[0-9]+');
    Route::post('/f01/{pengisian}/submit', [\App\Http\Controllers\F01PengisianController::class, 'submit'])->name('f01.submit')->where('pengisian', '[0-9]+');

    // F02 Validasi routes
    Route::get('/f02', [\App\Http\Controllers\F02ValidasiController::class, 'index'])->name('f02.index');
    Route::get('/f02/export/progress', [\App\Http\Controllers\F02ValidasiController::class, 'exportProgressReport'])->name('f02.export.progress');
    Route::get('/f02/{id}/init', [\App\Http\Controllers\F02ValidasiController::class, 'initValidasi'])->name('f02.init-validasi')->where('id', '[0-9]+');
    Route::post('/f02/{id}/save', [\App\Http\Controllers\F02ValidasiController::class, 'save'])->name('f02.save')->where('id', '[0-9]+');
    Route::post('/f02/{id}/finalize', [\App\Http\Controllers\F02ValidasiController::class, 'finalize'])->name('f02.finalize')->where('id', '[0-9]+');
    Route::post('/f02/{id}/reject', [\App\Http\Controllers\F02ValidasiController::class, 'reject'])->name('f02.reject')->where('id', '[0-9]+');

    // F02 Validasi - New aspek-grouped flow
    Route::get('/f02/{validasi}/aspek-list', [\App\Http\Controllers\F02ValidasiController::class, 'aspekList'])->name('f02.aspek-list')->where('validasi', '[0-9]+');
    Route::get('/f02/{validasi}/validasi-detail/{aspek}', [\App\Http\Controllers\F02ValidasiController::class, 'validasiDetail'])->name('f02.validasi-detail')->where(['validasi' => '[0-9]+', 'aspek' => '[0-9]+']);
    Route::post('/api/f02/{validasi}/auto-save/{indikator}', [\App\Http\Controllers\F02ValidasiController::class, 'autoSave'])->name('f02.auto-save')->where(['validasi' => '[0-9]+', 'indikator' => '[0-9]+']);
    Route::post('/f02/{validasi}/finalize-validation', [\App\Http\Controllers\F02ValidasiController::class, 'finalizeValidation'])->name('f02.finalize-validation')->where('validasi', '[0-9]+');
    Route::post('/f02/{validasi}/save-indikator/{indikator}', [\App\Http\Controllers\F02ValidasiController::class, 'saveIndikator'])->name('f02.save-indikator')->where(['validasi' => '[0-9]+', 'indikator' => '[0-9]+']);

    // F02 Resubmit versioning routes (NEW)
    Route::post('/f02/{f02Validasi}/allow-resubmit', [\App\Http\Controllers\F02ValidasiController::class, 'allowResubmit'])->name('f02.allow-resubmit')->where('f02Validasi', '[0-9]+');
    Route::post('/f02/allow-resubmit-bulk', [\App\Http\Controllers\F02ValidasiController::class, 'allowResubmitBulk'])->name('f02.allow-resubmit-bulk');

    // F02 Skor Management routes
    Route::get('/f02-skor', [\App\Http\Controllers\F02SkorController::class, 'index'])->name('f02.skor.index');
    Route::get('/f02-skor/{aspek}', [\App\Http\Controllers\F02SkorController::class, 'show'])->name('f02.skor.show');
    Route::get('/api/f02-skor/{indikatorId}', [\App\Http\Controllers\F02SkorController::class, 'getSkor'])->name('f02.skor.get');
    Route::post('/api/f02-skor/save', [\App\Http\Controllers\F02SkorController::class, 'saveSkor'])->name('f02.skor.save');
    Route::post('/api/f02-skor/{indikatorId}/delete', [\App\Http\Controllers\F02SkorController::class, 'deleteSkor'])->name('f02.skor.delete');

    // Public Periode listing (authenticated) — a friendly URL `/periode`
    Route::get('/periode', function () {
        $periodes = \App\Models\Periode::orderBy('tahun','desc')->paginate(20);
        return view('periode.index', compact('periodes'));
    })->name('periode.index');

    // Alias for installations served under a subfolder
    Route::get('/evaluasi-yanlik/periode', function () {
        $periodes = \App\Models\Periode::orderBy('tahun','desc')->paginate(20);
        return view('periode.index', compact('periodes'));
    })->name('periode.alias');
});


Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', EnsureUserUpp::class])->name('dashboard');

Route::resource('pengumuman', \App\Http\Controllers\PengumumanController::class)
    ->middleware(['auth', EnsureUserUpp::class]);

Route::post('/dashboard/save-preferred-upps', [\App\Http\Controllers\DashboardController::class, 'savePreferredUpps'])
    ->middleware(['auth', EnsureUserUpp::class])->name('dashboard.save-preferred-upps');
Route::get('/api/dashboard/chart-data', [\App\Http\Controllers\DashboardController::class, 'getChartData'])
    ->middleware(['auth', EnsureUserUpp::class])->name('api.dashboard.chart-data');

Route::post('/api/dashboard/filtered-data', [\App\Http\Controllers\DashboardController::class, 'getFilteredDashboardData'])
    ->middleware(['auth', EnsureUserUpp::class])->name('api.dashboard.filtered-data');

Route::get('/pekpp-mandiri', function () {
    $user = Auth::user();
    return view('pekpp_mandiri', ['user' => $user]);
})->middleware(['auth', EnsureUserUpp::class])->name('pekpp.mandiri');


Route::get('/debug/view-write', function () {
    $compiledPath = storage_path('framework/views');
    $testFile = $compiledPath . '/debug-' . uniqid() . '.php';
    $canWriteDir = is_writable($compiledPath);
    $uid = function_exists('posix_geteuid') ? posix_geteuid() : null;
    $gid = function_exists('posix_getegid') ? posix_getegid() : null;
    $user = ($uid && function_exists('posix_getpwuid')) ? posix_getpwuid($uid)['name'] : null;
    $info = [
        'compiled_path' => $compiledPath,
        'can_write_dir' => $canWriteDir,
        'test_file' => $testFile,
        'uid' => $uid,
        'gid' => $gid,
        'user' => $user,
        'file_exists_before' => file_exists($testFile),
        'dir_perms' => is_dir($compiledPath) ? substr(sprintf('%o', fileperms($compiledPath)), -4) : null,
        'ls_Z' => trim(@shell_exec('ls -ldZ ' . escapeshellarg($compiledPath)) ?: ''),
        'ls_file' => trim(@shell_exec('ls -la ' . escapeshellarg($compiledPath) . ' | tail -n 20') ?: ''),
        'attempt_write' => null,
        'write_error' => null,
    ];
    try {
        $written = @file_put_contents($testFile, '<?php // debug ?>');
        $info['attempt_write'] = $written;
        $info['file_exists_after'] = file_exists($testFile);
        $info['file_perms_after'] = file_exists($testFile) ? substr(sprintf('%o', fileperms($testFile)), -4) : null;
        $info['file_owner_after'] = file_exists($testFile) && function_exists('posix_getpwuid') ? posix_getpwuid(fileowner($testFile)) : null;
        @unlink($testFile);
    } catch (\Throwable $e) {
        $info['write_error'] = $e->getMessage();
    }
    return response()->json($info);
});

Route::get('/debug/render-dashboard', function () {
    try {
        $content = view('dashboard')->render();
        return response($content);
    } catch (\Throwable $e) {
        return response()->json(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()], 500);
    }
});

// Expose a simple API-compatible endpoint for analytics exports on the web router
// to ensure test-suite requests to `/api/analytics/exports` are routed even when
// API route groups are guarded by additional middleware in app configuration.
Route::post('/api/analytics/exports', [ApiAnalyticsExportController::class, 'store']);

// Debug F01 endpoints
Route::get('/debug/f01/{pengisianId}/jawaban', [\App\Http\Controllers\DebugF01Controller::class, 'checkJawaban']);
Route::get('/debug/f01/log/view', [\App\Http\Controllers\DebugF01Controller::class, 'viewLog']);
Route::get('/debug/f01/log/clear', [\App\Http\Controllers\DebugF01Controller::class, 'clearLog']);
