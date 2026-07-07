<?php

namespace App\Providers;

use App\Models\F01Pengisian;
use App\Models\F02Validasi;
use App\Observers\StatistikPublikObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('publik', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip());
        });

        // Provide a small set of globals used by many Blade templates to
        // avoid "Undefined variable" errors when controllers don't pass them.
        // Share the authenticated user object with views. Avoid sharing a Closure
        // (which would make `$user` a Closure in Blade). Use a composer so the
        // actual `Auth::user()` is passed at render time.
        View::composer('*', function ($view) {
            $view->with('user', Auth::user());
        });

        View::share('isActiveMenu', function (string $name) {
            try {
                $current = trim(request()->path(), '/');
            } catch (\Throwable $e) {
                $current = '';
            }
            $name = trim($name, '/');
            if ($current === $name) return true;
            if (Str::startsWith($current, $name . '/')) return true;
            if (Str::endsWith($current, '/' . $name)) return true;
            if (Str::contains($current, '/' . $name . '/')) return true;
            return false;
        });

        // Helper function for question type labels
        View::share('getQuestionTypeLabel', function (string $tipe) {
            $labels = [
                'text' => 'Teks Pendek',
                'textarea' => 'Teks Panjang',
                'number' => 'Angka',
                'radio' => 'Pilihan Ganda',
                'checkbox' => 'Pilihan Banyak',
                'select' => 'Dropdown',
                'yesno' => 'Ya/Tidak',
                'skala' => 'Skala'
            ];
            return $labels[$tipe] ?? $tipe;
        });

        // Provide prepared user identity data specifically for the topbar.
        // This moves logic out of the Blade and into a View Composer as required.
        View::composer('partials.topbar', function ($view) {
            $user = Auth::user();

            $user_name = '';
            $user_role_label = '';
            $user_opd_name = '';
            $user_avatar_initial = '-';

            try {
                if ($user) {
                    $user_name = $user->nama ?? $user->name ?? '';
                    // Prefer an explicit SSO role if present, otherwise try first UPP peran
                    if (!empty($user->role_sso)) {
                        $user_role_label = $user->role_sso;
                    } elseif (method_exists($user, 'getUserUpps')) {
                        $first = $user->getUserUpps()->first();
                        if ($first) {
                            $user_role_label = $first->peran ?? '';
                            $user_opd_name = optional($first->upp)->nama ?? '';
                        }
                    }

                    $trimmed = trim((string) $user_name);
                    if ($trimmed !== '') {
                        $user_avatar_initial = mb_strtoupper(mb_substr($trimmed, 0, 1));
                    }
                }
            } catch (\Throwable $e) {
                // swallow errors - topbar must not crash the app
            }

            $view->with([
                'user_name' => $user_name,
                'user_role_label' => $user_role_label,
                'user_opd_name' => $user_opd_name,
                'user_avatar_initial' => $user_avatar_initial,
            ]);
        });

        // Inject a lightweight scope_context into job payloads when jobs are pushed.
        // This ensures background jobs have traceable tenant/user context without
        // modifying every job constructor.
        Queue::createPayloadUsing(function () {
            try {
                $user = Auth::user();
                if (! $user) return [];

                $roles = [];
                if (method_exists($user, 'getRoleNames')) {
                    $roles = $user->getRoleNames()->toArray();
                }

                return [
                    'scope_context' => [
                        'tenant_id' => $user->tenant_id ?? $user->tenantId ?? null,
                        'user_id' => $user->id ?? null,
                        'roles' => $roles,
                        'scope_key' => null,
                        'correlation_id' => null,
                    ],
                ];
            } catch (\Throwable $e) {
                return [];
            }
        });

        F01Pengisian::observe(StatistikPublikObserver::class);
        F02Validasi::observe(StatistikPublikObserver::class);
    }
}
