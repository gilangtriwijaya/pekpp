# Horizon & Browsershot Setup Notes

Recommended Horizon supervisor config for analytics queues (example snippet for `config/horizon.php` or deploy manifest):

```
'supervisors' => [
  'analytics' => [
    'connection' => 'redis',
    'queue' => ['exports', 'aggregates', 'default'],
    'balance' => 'simple',
    'processes' => 5,
    'tries' => 3,
  ],
]
```

Browsershot (headless Chromium) guidance:
- Preferred: run a browserless sidecar (`browserless/chrome`) and point `BROWSERSHOT_CHROME_PATH` to the sidecar URL (e.g., `http://browserless:3000`).
- Alternative: embed Chromium in the worker image (use `zenika/alpine-chrome` base image) and set `PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=true`.

Health checks:
- Ensure Chromium sidecar exposes a `/health` endpoint and that worker startup waits for it.

Ops checklist:
- Redis available for Horizon and rate-limit counters.
- S3 credentials configured for `analytics.storage_disk = s3`.
- Ensure worker images include any required binaries if embedding Chromium.
