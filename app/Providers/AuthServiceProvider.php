<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\F01Pengisian;
use App\Policies\F01PengisianPolicy;
use App\Models\AnalyticsExport;
use App\Policies\AnalyticsExportPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        F01Pengisian::class => F01PengisianPolicy::class,
        AnalyticsExport::class => AnalyticsExportPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Gate::before(function ($user, string $ability) {
            return (method_exists($user, 'hasRole') && $user->hasRole('superadmin')) ? true : null;
        });

        // Note: per request, controllers now perform direct role checks
        // (superadmin/admin_organisasi/admin_bagian_organisasi). Keep other gates as-is.

        // Modify structure guard: allowed role AND no finalized pengisian
        Gate::define('modify-f01-structure', function ($user) {
            $role = strtolower(trim($user->role_sso ?? ''));
            if (! in_array($role, ['superadmin', 'admin_organisasi'])) return false;
            return ! \App\Models\F01Pengisian::where('status', 'final')->exists();
        });
    }
}
