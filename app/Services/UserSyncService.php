<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\SsoAllowedOpd;
use App\Models\Opd;
 
use Illuminate\Support\Str;

class UserSyncService
{
    /**
     * Sync or create a local user from SSO payload.
     * Accepts optional mapped opd/opd_unit ids (local ids).
     *
     * @param array $ssoUser
     * @param array $mappedIds ['opd_id'=>?int,'opd_unit_id'=>?int]
     * @return User
     */
    public function syncFromPayload(array $ssoUser, array $mappedIds = []): User
    {
        $payload = [
            'sso_user_id'  => (int)($ssoUser['id'] ?? 0),
            'nama'         => $ssoUser['name'] ?? 'User',
            'email'        => !empty($ssoUser['email']) ? $ssoUser['email'] : null,
            'nip'          => $ssoUser['nip'] ?? null,
            'role_sso'     => '',
            'aktif'        => (int)($ssoUser['is_active'] ?? 1),
            'last_sync_at' => now(),
        ];

        // Only include email if provided and non-empty to avoid overwriting non-null DB constraint
        if (! empty($ssoUser['email'])) {
            $payload['email'] = $ssoUser['email'];
        }

        $user = DB::transaction(function () use ($payload) {
            return User::updateOrCreate(
                ['sso_user_id' => $payload['sso_user_id']],
                $payload
            );
        });

        // Do NOT sync SSO/app roles into local authorization tables here.
        // Persist a simple `role_sso` string for audit/debug only below.

        // Persist SSO metadata fields on user for debugging / audit (minimal)
        try {
            $user->forceFill([
                'role_sso' => is_string($ssoUser['app_role'] ?? null) ? $ssoUser['app_role'] : '',
                'last_sync_at' => now(),
            ])->save();
        } catch (\Throwable $e) {
            Log::warning('Failed to persist sso metadata: '.$e->getMessage());
        }

        // =================================================================
        // Persist allowed OPD mapping per app into sso_allowed_opds table
        // =================================================================
        $appCode = (string) config('services.sso.app_code', env('SSO_APP_CODE', 'pekpp'));
        $byApp = $ssoUser['allowed_opd_ids_by_app'] ?? null;

        // Determine allowed for this app according to priority rules.
        $allowed = null;
        if (is_array($byApp) && array_key_exists($appCode, $byApp)) {
            $allowed = $byApp[$appCode];
        } else {
            $allowed = $ssoUser['allowed_opd_ids'] ?? null;
        }

        if (array_key_exists('is_opd_locked', $ssoUser) && $ssoUser['is_opd_locked'] === false) {
            // explicit unlocked -> treat as GLOBAL
            $allowed = null;
        }

        if (is_array($allowed) && count($allowed) === 0) {
            // empty array treated as GLOBAL per spec
            $allowed = null;
        }

        // replace existing mapping for this user+app
        try {
            SsoAllowedOpd::where('user_id', $user->id)->where('app_code', $appCode)->delete();

            if (is_array($allowed)) {
                foreach ($allowed as $maybeSsoOpdId) {
                    // SSO typically provides SSO OPD ids. Try to map to local opd.id via sso_id.
                    $ssoOpdId = (int) $maybeSsoOpdId;
                    $localOpdId = Opd::where('sso_id', $ssoOpdId)->value('id');

                    // Fallback: if payload included codes (not typical here) we could try map by code/name.
                    if (! $localOpdId) {
                        // try by id as last resort (backwards compatibility)
                        if (Opd::where('id', $ssoOpdId)->exists()) {
                            $localOpdId = $ssoOpdId;
                        }
                    }

                    if (! $localOpdId) {
                        Log::warning("SSO allowed OPD sso_id {$ssoOpdId} for user {$user->sso_user_id} not found locally; skipped");
                        continue;
                    }

                    SsoAllowedOpd::create([
                        'user_id' => $user->id,
                        'app_code' => $appCode,
                        'opd_id' => $localOpdId,
                    ]);
                }
            } else {
                // GLOBAL -> nothing to insert (we already deleted old rows)
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to sync sso_allowed_opds: '.$e->getMessage());
        }

        return $user;
    }

    /**
     * Normalize an incoming role string or slug into a canonical local role name.
     * Examples: 'verifikator-global'|'verifikator_global' -> 'verifikator global'
     */
    private function normalizeRoleName(string $in): ?string
    {
        $s = trim($in);
        if ($s === '') return null;
        $low = mb_strtolower($s);
        // replace separators with space
        $low = preg_replace('/[_\-]+/', ' ', $low);
        $low = preg_replace('/\s+/', ' ', $low);

        // common mappings
        if (str_contains($low, 'verifikator')) {
            if (str_contains($low, 'global') || str_contains($low, 'bagor') || str_contains($low, 'lintas')) {
                return 'verifikator global';
            }
            if (str_contains($low, 'opd')) {
                return 'verifikator opd';
            }
            if (str_contains($low, 'unit')) {
                return 'verifikator unit';
            }
        }

        if (str_contains($low, 'admin')) {
            if (str_contains($low, 'bagor') || str_contains($low, 'organisasi')) {
                return 'admin bagor';
            }
            if (str_contains($low, 'opd')) {
                return 'admin opd';
            }
            if (str_contains($low, 'unit')) {
                return 'admin unit';
            }
        }

        // fallback: return cleaned lowercase phrase
        return $low;
    }

}
