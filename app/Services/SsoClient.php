<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SsoClient
{
    protected string|null $base;
    protected string|null $opdsEndpoint;
    protected string|null $usersEndpoint;
    protected string|null $token;
    protected string|null $secret;
    protected string $authMethod = 'bearer';

    public function __construct()
    {
        $this->base = rtrim(config('services.sso.base_url', env('SSO_BASE_URL', '')), '/');
        $this->opdsEndpoint = config('services.sso.opds_endpoint', env('SSO_OPDS_ENDPOINT', '/api/sso/opds'));
        $this->usersEndpoint = config('services.sso.users_endpoint', env('SSO_USERS_ENDPOINT', '/api/sso/users'));
        // support plural env names for rotation: SSO_PULL_TOKENS, SSO_PULL_SECRETS
        $tokensEnv = config('services.sso.pull_tokens', env('SSO_PULL_TOKENS', env('SSO_PULL_TOKEN')));
        $secretsEnv = config('services.sso.pull_secrets', env('SSO_PULL_SECRETS', env('SSO_PULL_SECRET')));

        $this->token = null;
        if (!empty($tokensEnv)) {
            $parts = array_filter(array_map('trim', explode(',', (string)$tokensEnv)));
            if (!empty($parts)) $this->token = $parts[0];
        }

        $this->secret = null;
        if (!empty($secretsEnv)) {
            $parts = array_filter(array_map('trim', explode(',', (string)$secretsEnv)));
            if (!empty($parts)) $this->secret = $parts[0];
        }

        // prefer HMAC if secret is present
        if (!empty($this->secret)) $this->authMethod = 'hmac';
    }

    /**
     * Fetch one page of users from SSO pull endpoint.
     * Returns associative array with keys: data (array), meta (if present)
     */
    public function fetchUsersPage(int $page = 1, int $perPage = 100, string|int|null $updatedAfter = null): array
    {
        if (empty($this->base) || empty($this->usersEndpoint)) {
            throw new \RuntimeException('SSO base URL or users endpoint not configured (SSO_BASE_URL / SSO_USERS_ENDPOINT)');
        }

        $url = $this->base . $this->usersEndpoint;
        $params = ['page' => $page, 'per_page' => $perPage];
        $app = config('services.sso.app_code', env('SSO_APP_CODE'));
        if (!empty($app)) $params['app'] = $app;
        if (!is_null($updatedAfter) && $updatedAfter !== '') $params['updated_after'] = $updatedAfter;

        $client = Http::timeout(30)->retry(1, 200);

        if ($this->authMethod === 'hmac' && !empty($this->secret)) {
            $ts = (string) time();
            // GET request has empty body
            $raw = '';
            $sig = hash_hmac('sha256', $ts.'.'.$raw, $this->secret);
            $headers = [
                'X-SSO-Timestamp' => $ts,
                'X-SSO-Signature' => $sig,
                'Accept' => 'application/json',
            ];
            $req = $client->withHeaders($headers)->get($url, $params);
        } elseif (!empty($this->token)) {
            $req = $client->withToken($this->token)->acceptJson()->get($url, $params);
        } else {
            throw new \RuntimeException('No SSO pull auth configured (SSO_PULL_TOKEN or SSO_PULL_SECRET)');
        }

        if (! $req->successful()) {
            throw new \RuntimeException('SSO fetch users failed: ' . $req->status() . ' ' . substr($req->body(), 0, 400));
        }

        $json = $req->json() ?: [];
        // SSO returns keys like 'items' with pagination fields 'total','page','per_page'.
        $data = $json['data'] ?? $json['items'] ?? [];
        $meta = [];
        if (isset($json['total']) && isset($json['per_page'])) {
            $per = max(1, (int)$json['per_page']);
            $total = (int)$json['total'];
            $meta['last_page'] = (int) ceil($total / $per);
            $meta['page'] = (int) ($json['page'] ?? 1);
            $meta['per_page'] = $per;
        }
        return ['data' => $data, 'meta' => $meta];
    }

    /**
     * Convenience for fetching OPDs pages (reuse patterns)
     */
    public function fetchOpdsPage(int $page = 1, int $perPage = 50, string|int|null $updatedAfter = null): array
    {
        if (empty($this->base) || empty($this->opdsEndpoint)) {
            throw new \RuntimeException('SSO base URL or opds endpoint not configured (SSO_BASE_URL / SSO_OPDS_ENDPOINT)');
        }

        $url = $this->base . $this->opdsEndpoint;
        $params = ['page' => $page, 'per_page' => $perPage];
        $app = config('services.sso.app_code', env('SSO_APP_CODE'));
        if (!empty($app)) $params['app'] = $app;
        if (!is_null($updatedAfter) && $updatedAfter !== '') $params['updated_after'] = $updatedAfter;

        $client = Http::timeout(30)->retry(1, 200);
        if ($this->authMethod === 'hmac' && !empty($this->secret)) {
            $ts = (string) time();
            $raw = '';
            $sig = hash_hmac('sha256', $ts.'.'.$raw, $this->secret);
            $headers = [
                'X-SSO-Timestamp' => $ts,
                'X-SSO-Signature' => $sig,
                'Accept' => 'application/json',
            ];
            $req = $client->withHeaders($headers)->get($url, $params);
        } elseif (!empty($this->token)) {
            $req = $client->withToken($this->token)->acceptJson()->get($url, $params);
        } else {
            throw new \RuntimeException('No SSO pull auth configured (SSO_PULL_TOKEN or SSO_PULL_SECRET)');
        }

        if (! $req->successful()) {
            throw new \RuntimeException('SSO fetch OPDs failed: ' . $req->status() . ' ' . substr($req->body(), 0, 400));
        }

        $json = $req->json() ?: [];
        $data = $json['data'] ?? $json['items'] ?? [];
        $meta = [];
        if (isset($json['total']) && isset($json['per_page'])) {
            $per = max(1, (int)$json['per_page']);
            $total = (int)$json['total'];
            $meta['last_page'] = (int) ceil($total / $per);
            $meta['page'] = (int) ($json['page'] ?? 1);
            $meta['per_page'] = $per;
        }
        return ['data' => $data, 'meta' => $meta];
    }

    /**
     * Convenience for fetching OPD units pages
     */
    public function fetchOpdUnitsPage(int $page = 1, int $perPage = 50, string|int|null $updatedAfter = null): array
    {
        // reuse same pattern as fetchOpdsPage but call opd-units endpoint
        $endpoint = config('services.sso.opd_units_endpoint', env('SSO_OPD_UNITS_ENDPOINT', '/api/sso/opd-units'));
        if (empty($this->base) || empty($endpoint)) {
            throw new \RuntimeException('SSO base URL or opd-units endpoint not configured (SSO_BASE_URL / SSO_OPD_UNITS_ENDPOINT)');
        }

        $url = $this->base . $endpoint;
        $params = ['page' => $page, 'per_page' => $perPage];
        $app = config('services.sso.app_code', env('SSO_APP_CODE'));
        if (!empty($app)) $params['app'] = $app;
        if (!is_null($updatedAfter) && $updatedAfter !== '') $params['updated_after'] = $updatedAfter;

        $client = Http::timeout(30)->retry(1, 200);
        if ($this->authMethod === 'hmac' && !empty($this->secret)) {
            $ts = (string) time();
            $raw = '';
            $sig = hash_hmac('sha256', $ts.'.'.$raw, $this->secret);
            $headers = [
                'X-SSO-Timestamp' => $ts,
                'X-SSO-Signature' => $sig,
                'Accept' => 'application/json',
            ];
            $req = $client->withHeaders($headers)->get($url, $params);
        } elseif (!empty($this->token)) {
            $req = $client->withToken($this->token)->acceptJson()->get($url, $params);
        } else {
            throw new \RuntimeException('No SSO pull auth configured (SSO_PULL_TOKEN or SSO_PULL_SECRET)');
        }

        if (! $req->successful()) {
            throw new \RuntimeException('SSO fetch OPD units failed: ' . $req->status() . ' ' . substr($req->body(), 0, 400));
        }

        $json = $req->json() ?: [];
        $data = $json['data'] ?? $json['items'] ?? [];
        $meta = [];
        if (isset($json['total']) && isset($json['per_page'])) {
            $per = max(1, (int)$json['per_page']);
            $total = (int)$json['total'];
            $meta['last_page'] = (int) ceil($total / $per);
            $meta['page'] = (int) ($json['page'] ?? 1);
            $meta['per_page'] = $per;
        }
        return ['data' => $data, 'meta' => $meta];
    }
}
