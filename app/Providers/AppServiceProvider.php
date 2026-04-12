<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;

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
    }
}
