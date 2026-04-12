<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\UserSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\ActivityLog;

class SsoLoginController extends Controller
{
    private function ssoBase(): string
    {
        $base = rtrim(config('services.sso.base_url', env('SSO_BASE_URL', '')), '/');

        if ($base === '') abort(500, 'SSO_BASE_URL not set');

        if (Str::startsWith($base, [
            'http://127.0.0.1','http://localhost',
            'https://127.0.0.1','https://localhost',
        ])) {
            abort(500, 'SSO_BASE_URL points to localhost; set it to https://sistagor.anambaskab.go.id');
        }

        if (!Str::startsWith($base, 'https://')) {
            abort(500, 'SSO_BASE_URL must start with https://');
        }

        return $base;
    }

    private function appCode(): string
    {
        return (string) config('services.sso.app_code', env('SSO_APP_CODE', 'pekpp'));
    }

    private function secret(): string
    {
        $secret = (string) config('services.sso.ticket_secret', env('SSO_TICKET_SECRET', ''));
        if ($secret === '') abort(500, 'SSO_TICKET_SECRET not set');
        return $secret;
    }

    public function redirectToSso(Request $request)
    {
        $intended = url()->previous();
        if (!$intended || Str::contains($intended, ['/sso/callback', '/sso/login'])) {
            $intended = url('/');
        }

        $state = Str::random(24);

        session([
            'sso.intended' => $intended,
            'sso.state'    => $state,
        ]);

        $ssoBase = $this->ssoBase();
        $appCode = $this->appCode();
        $callbackUrl = url('/sso/callback');

        Log::info('SSO redirect initiated', [
            'app'      => $appCode,
            'sso_base' => $ssoBase,
            'callback' => $callbackUrl,
            'intended' => $intended,
            'state'    => substr($state, 0, 8) . '...',
        ]);

        return redirect()->away($ssoBase . '/sso/authorize?' . http_build_query([
            'app'          => $appCode,
            'redirect_uri' => $callbackUrl,
            'state'        => $state,
        ]));
    }

    public function callback(Request $request)
    {
        $request->validate([
            'ticket' => ['required', 'string'],
            'state'  => ['nullable', 'string'],
        ]);

        $expectedState = session('sso.state');
        session()->forget('sso.state');

        if ($expectedState && $request->filled('state') && !hash_equals($expectedState, (string) $request->state)) {
            Log::warning('SSO state mismatch', [
                'expected' => substr($expectedState, 0, 8) . '...',
                'got'      => substr((string) $request->state, 0, 8) . '...',
            ]);
            abort(419, 'Invalid SSO state');
        }

        $ssoBase = $this->ssoBase();
        $appCode = $this->appCode();
        $secret  = $this->secret();

        $ticket = (string) $request->ticket;
        $signature = hash_hmac('sha256', $ticket . '|' . $appCode, $secret);

        $url = $ssoBase . '/api/sso/ticket/consume';

        Log::info('SSO consume request', [
            'url'           => $url,
            'app'           => $appCode,
            'ticket_prefix' => substr($ticket, 0, 10) . '...',
            'sig_prefix'    => substr($signature, 0, 12) . '...',
        ]);

        try {
            $resp = Http::withHeaders([
                    'X-SSO-Signature' => $signature,
                    'Accept'          => 'application/json',
                ])
                ->asForm()
                ->timeout(15)
                ->retry(1, 200)
                ->post($url, [
                    'ticket' => $ticket,
                    'app'    => $appCode,
                ]);
        } catch (\Throwable $e) {
            Log::error('SSO consume connection failed', [
                'url'   => $url,
                'error' => $e->getMessage(),
            ]);
            abort(502, 'Failed to connect to SSO');
        }

        if (!$resp->successful()) {
            Log::warning('SSO consume non-2xx', [
                'status' => $resp->status(),
                'body'   => Str::limit($resp->body(), 800),
            ]);
            abort(401, 'SSO ticket invalid');
        }

        $data = $resp->json();
        $ssoUser = $data['user'] ?? null;

        // try apply SSO payload into local DB so login can rely on local user
        try {
            if (is_array($ssoUser)) {
                $syncSvc = app(UserSyncService::class);
                $local = $syncSvc->syncFromPayload($ssoUser);
                if ($local && $local->id) {
                    Log::info('SSO payload applied during login', ['sso_user_id' => $ssoUser['id'] ?? null, 'local_user_id' => $local->id]);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Failed applying SSO payload during login', ['err' => $e->getMessage(), 'sso_user' => $ssoUser['id'] ?? null]);
        }

        if (!is_array($ssoUser) || empty($ssoUser['email'])) {
            Log::warning('SSO payload invalid', ['payload' => $data]);
            abort(401, 'SSO user payload invalid');
        }

        $localUser = User::where('email', $ssoUser['email'])->first();

        if (!$localUser) {
            Log::warning('SSO user not found in PEKPP DB', [
                'email' => $ssoUser['email'],
                'sso_user_id' => $ssoUser['id'] ?? null,
            ]);
            abort(403, 'User SSO belum terdaftar di PEKPP');
        }

        $updates = [];
        if (!empty($ssoUser['id']) && ($localUser->sso_user_id ?? null) !== (int)$ssoUser['id']) {
            $updates['sso_user_id'] = (int)$ssoUser['id'];
        }
        if (!empty($ssoUser['app_role_slug'])) {
            // Store app role info in role_sso field
            $updates['role_sso'] = $ssoUser['app_role_slug'];
        }
        if (!empty($updates)) {
            $updates['last_sync_at'] = now();
            $localUser->update($updates);
            Log::info('SSO login updated local user with SSO id', ['local_user_id' => $localUser->id, 'updates' => $updates]);
        }

        Auth::login($localUser);
        // record successful SSO login
        try {
            ActivityLog::record('login', ['params' => ['sso_user_id' => $ssoUser['id'] ?? null, 'email' => $ssoUser['email'] ?? null]]);
        } catch (\Throwable $e) {
            Log::warning('ActivityLog record failed on login: '.$e->getMessage());
        }
        $request->session()->regenerate();

        session(['sso.user' => $ssoUser]);

        $intended = session('sso.intended');
        session()->forget('sso.intended');

        return redirect()->to($intended ?: url('/dashboard'));
    }

    public function backToSso(Request $request)
    {
        $request->session()->forget(['sso.user', 'sso.intended', 'sso.state']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $home = (string) config('services.sso.home_url', '') ?: $this->ssoBase();
        return redirect()->away($home);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // record logout
        try {
            ActivityLog::record('logout');
        } catch (\Throwable $e) {
            Log::warning('ActivityLog record failed on logout: '.$e->getMessage());
        }

        // After logout, send the user back to the SSO dashboard/home page
        $home = (string) config('services.sso.home_url', '') ?: $this->ssoBase();
        return redirect()->away($home);
    }
}
