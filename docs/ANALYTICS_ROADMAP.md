# ROADMAP: Analisis Data & Export — Desain Produksi Lengkap

Versi: 2026-04-16
Penulis: Tim Pengembang / Arsitek

Ringkasan eksekutif
- Tujuan: membangun modul "Analisis" terpisah yang siap produksi — scalable, aman, dan mudah dipelihara — untuk analisis data F02/F03, visualisasi infografis, drill-down, dan export CSV/PDF yang andal.
- Keunggulan rancangan ini: memisahkan beban operasional (dashboard) dari analitik berat, menyediakan precompute untuk performa, serta export asynchronous dan audit untuk keamanan dan observabilitas.

Konteks dan asumsi (perlu dikonfirmasi)
- Aplikasi utama sudah berjalan di Laravel (v12) dengan MySQL sebagai DB utama.
- Sumber data analitik: tabel F02 dan F03; semua agregasi wajib memfilter `is_latest_version = 1`.
- Infrastruktur ideal tersedia: Redis (queue/cache), S3 atau storage yang mendukung signed URL, dan worker pool (Horizon recommended).
- Asumsi volume data per periode harus ditentukan (estimasi rows F02 per periode: <100k / 100k–1M / >1M) — pengambilan keputusan arsitektural bergantung pada angka ini.

Tujuan bisnis
- Menyediakan analisis mendalam per periode, provinsi, OPD, UPP, aspek, indikator, dan status validasi.
- Menyediakan KPI ringkasan, tren waktu, distribusi skor, ranking UPP, drill-down, dan reporting terjadwal.
- Menyediakan export CSV/PDF yang andal, audit-ready, dan aman (signed URL & role-based access).

Ruang lingkup (full implementation — bukan MVP)
- Menu terpisah `Analisis` di sidebar dengan akses terbatas (role: `admin`, `analyst`).
- Halaman analisis penuh: filter lengkap, kartu KPI, line/bar/radar/histogram, tabel detail indikator dengan server-side pagination, dan drill-down interaktif.
- Export: CSV streaming & queued, PDF templated via headless engine, notifications & signed download links.
- Backend: service layer, precomputed aggregates (`analytics_aggregates`), queue jobs, caching.
- Security: RBAC via Spatie, tenant scoping, audit logs, signed URLs.
- Ops: monitoring (Horizon), retention & cleanup, retry/backoff policies, SLA untuk exports.

Non-goals
- Menambahkan real-time analytics push (WebSocket) pada fase awal.
- Re-architecting seluruh data model F02/F03 — asumsi struktur data relevan dan konsisten.

Detil fungsional & teknis

Ringkasan Metrik Inti
- **total_submits**: Jumlah total pengisian/submit untuk periode dan scope yang dipilih. Sumber: tabel F02/F03 (dengan filter `is_latest_version = 1`). Hitung sebagai COUNT(*); nilai kosong diperlakukan sebagai 0.
- **total_validated**: Jumlah entri dengan `validated = 1` pada scope yang dipilih. Digunakan untuk perhitungan rasio validasi (`pct_validated`).
- **avg_IPP**: Rata-rata skor IPP yang dibulatkan dua desimal (AVG(score)). Nilai NULL diabaikan.
- **pending_count**: Jumlah entri yang belum tervalidasi (`validated = 0`) atau dianggap kosong menurut aturan bisnis (mengindikasikan backlog verifikasi).
- **ranking**: Urutan UPP berdasarkan `avg_IPP` (desc). Tie-breaker: `total_responses` desc, lalu `computed_at` terbaru.
- **median**: Median skor per grup — dihitung di job aggregator (PHP) untuk kestabilan performa; jangan hitung median on-the-fly untuk set besar.
- **distribusi**: Distribusi skor (histogram) dengan bin preset server-side; frontend menerima nama preset (mis. `default_5bins`).

Catatan: ringkasan metrik singkat ini dimaksudkan agar pemangku kepentingan non-teknis dapat memahami metrik inti tanpa membuka `docs/ANALYTICS_METRICS.md`.

1) Filter & navigasi

2) Visualisasi & UX
  - Line: trend IPP per periode (series per UPP atau aggregated level).
  - Bar: avg score per aspek.
  - Radar: ringkasan skor per aspek (satu UPP atau agregat grup).
  - Histogram: distribusi skor (binned), configurable bins (mis. 0.0–0.5, 0.5–1.0 …).

3) Eksport (CSV & PDF)
- CSV:
  - **Idempotency required:** Semua permintaan export wajib menyertakan header `Idempotency-Key`. Server akan menolak atau mencegah duplikasi dalam jendela idempotency yang dapat dikonfigurasi; jika header tidak tersedia server dapat menghitung fallback, namun kebijakan implementasi harus memprioritaskan client-provided key.
  - Small result sets (estimasi rows <= 50k): synchronous streaming response (`response()->stream`) atau `maatwebsite/excel` export.
  - Large result sets: queued `GenerateAnalyticsCsvJob` that writes to `storage/app/exports/analytics/` then update `analytics_exports` record.
  - Implementation detail: prefer `chunkById` and `fputcsv` for memory safety when the source/query guarantees a stable, monotonically-increasing `id`. Jika tidak ada id stabil, gunakan `cursor()` atau ordered chunking untuk menghindari baris yang terlewat atau duplikat; selalu pastikan ordering deterministik pada sumber eksport.
- PDF:
  - For complex print-ready layouts with charts: use `snappy/wkhtmltopdf` (or headless Chromium) to ensure fidelity.
  - For simple templated reports: `dompdf` acceptable but may fail on complex graphics.
  - PDF generation must run in queue and produce signed URL on completion.
  - Production guidance for headless Chromium (Browsershot):
    - Preferred engine: `spatie/browsershot` (headless Chromium). Run Chromium in the worker container or as a sidecar/service (browserless) to avoid bulky binaries in every image.
    - Recommended images/approaches:
      - Sidecar/service: `browserless/chrome` or `browserless/chrome:latest` (run as a separate service with a health endpoint).
      - Embedded: `zenika/alpine-chrome` or a custom worker image that includes a stable Chromium binary.
    - Env vars and packaging: set `BROWSERSHOT_CHROME_PATH` to the binary path and `PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=true` when you provide your own binary in the image. Document required packages in ops runbook.
    - Health checks: if using a sidecar, probe `/health` or equivalent; if embedding Chromium, add a startup check that `which chromium` or `chromium --version` returns successfully before worker registers. Add a smoke check job that renders a small HTML to PDF on startup.
- Notifications: `SendAnalyticsExportNotificationJob` sends email (or in-app) with signed URL and metadata.

- CSV streaming code pattern (controller):

```php
public function streamCsvResponse(array $params)
{
  $fileName = 'analytics_export_'.now()->format('Ymd_His').'.csv';
  $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=\"$fileName\"",];

  $callback = function() use ($params) {
    $handle = fopen('php://output', 'w');
    fputcsv($handle, ['periode_id','upp_id','upp_nama','aspek_id','aspek_nama','indikator_id','indikator_nama','total_responses','avg_score','pct_validated']);
    $query = $this->buildAggregateQuery($params);
    $query->chunkById(1000, function($rows) use ($handle) {
      // stop if client disconnected
      if (function_exists('connection_aborted') && connection_aborted()) {
        return false; // stop further chunking
      }
      foreach ($rows as $row) {
        if (function_exists('connection_aborted') && connection_aborted()) {
          return false;
        }
        fputcsv($handle, [...]);
      }
    });
    fclose($handle);
  };

  return response()->stream($callback, 200, $headers);
}
```

4) Backend components & Contracts
- Controllers:
  - `AnalyticsController` — page + JSON endpoints for chart data and drill-down.
  - `AnalyticsExportController` — export endpoints (trigger, status, download, retry).
  - `AnalyticsReportController` — schedule/create automated reports.
- Services:
  - `AnalyticsReadService` — read-only service responsible for mapping metrics to their source of truth and returning KPI/chart/table data. Default read path is `analytics_aggregates`; may fall back to live queries for drill-downs (controlled via `prefer_aggregates` flag). Accepts `ScopeContext` and enforces read limits.
  - `AnalyticsAggregationService` — rebuild/incremental aggregator into `analytics_aggregates`.
  - `AnalyticsExportService` — create export logs, estimate size, decide queue/sync, and helper streaming.
  - `AnalyticsPdfService` — render blade to HTML + convert to PDF via engine.
- Jobs:
  - `GenerateAnalyticsCsvJob` (queued; chunk, write file, update log, dispatch notification).
  - `GenerateAnalyticsPdfJob` (queued; render & convert, update log, dispatch notification).
  - `RebuildAnalyticsAggregatesJob` (scheduled/incremental full or partial rebuild).
  - `SendAnalyticsExportNotificationJob` (email/in-app).
  - `CleanOldExportFilesJob` (scheduled daily to enforce retention).
- Models:
  - `AnalyticsAggregate` (Eloquent, read-only mostly).
  - `AnalyticsExport` (log, status, params, file_path, error_message).
  - `AnalyticsReportSchedule` (user-owned recurring reports).

5) Data model & migrations (contoh)
- `analytics_aggregates` (precompute store)

  Migration (ringkas):

```php
Schema::create('analytics_aggregates', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('periode_id')->index();
  $table->unsignedBigInteger('tenant_id')->nullable()->index();
  $table->string('scope_key')->nullable()->index();
  $table->enum('level', ['indicator','aspek','upp','opd','provinsi'])->default('indicator')->index();
  $table->string('dimension_hash')->nullable()->index();
  $table->unsignedInteger('aggregate_version')->default(1);
  $table->unsignedBigInteger('upp_id')->nullable()->index();
  $table->unsignedBigInteger('aspek_id')->nullable()->index();
  $table->unsignedBigInteger('indikator_id')->nullable()->index();
    $table->unsignedBigInteger('total_responses')->default(0);
    $table->decimal('avg_score', 6, 2)->default(0.00);
    $table->decimal('median_score', 6, 2)->default(0.00);
    $table->decimal('pct_validated', 5, 2)->default(0.00);
    $table->decimal('pct_empty', 5, 2)->default(0.00);
    $table->timestamp('computed_at')->nullable();
    $table->timestamps();
  $table->unique(['periode_id','scope_key','dimension_hash'], 'analytics_agg_unique');
});
```

- `analytics_exports` (log & file meta)
`analytics_exports` (log & file meta)

```php
Schema::create('analytics_exports', function (Blueprint $table) {
  $table->id();
  $table->unsignedBigInteger('user_id')->nullable()->index();
  $table->unsignedBigInteger('tenant_id')->nullable()->index();
  $table->string('scope_key')->nullable()->index();
  $table->string('idempotency_key')->nullable()->unique()->index();
  $table->string('correlation_id')->nullable()->index();
  $table->enum('type', ['csv', 'pdf']);
  $table->json('params')->nullable();
  $table->string('file_path')->nullable();
  $table->unsignedBigInteger('file_size')->nullable();
  $table->enum('status', ['pending','processing','ready','failed'])->default('pending')->index();
  $table->text('error_message')->nullable();
  $table->unsignedBigInteger('processed_rows')->default(0);
  $table->unsignedBigInteger('total_rows_estimate')->nullable();
  $table->decimal('progress_percent', 5, 2)->default(0.00);
  $table->unsignedInteger('idempotency_attempts')->default(0);
  $table->timestamp('last_attempted_at')->nullable();
  $table->timestamp('started_at')->nullable();
  $table->timestamp('finished_at')->nullable();
  $table->timestamps();
});
```

- `analytics_report_schedules` (scheduled reports)
`analytics_report_schedules` (scheduled reports)

```php
Schema::create('analytics_report_schedules', function (Blueprint $table) {
  $table->id();
  $table->string('name');
  $table->unsignedBigInteger('user_id')->nullable()->index();
  $table->enum('frequency',['daily','weekly','monthly']);
  $table->json('params')->nullable();
  $table->boolean('enabled')->default(true);
  $table->timestamp('last_run_at')->nullable();
  $table->timestamp('next_run_at')->nullable()->index();
  $table->enum('last_run_status', ['success','failed','skipped'])->nullable();
  $table->unsignedBigInteger('last_export_id')->nullable()->index();
  $table->timestamps();
});
```

Indexing & partitioning recommendations
- Indexes: `periode_id`, `upp_id`, `aspek_id`, `indikator_id`, `computed_at`.
- Source tables (F02/F03): ensure indexes on `periode_id`, `upp_id`, `aspek_id`, `is_latest_version`.
- Partitioning: for very large datasets, partition `analytics_aggregates` by RANGE on `periode_id` (e.g., year) or use native MySQL partitioning on epoch of `periode_id` if feasible.

6) Agregasi & strategi precompute
- Pilihan: (A) Real-time aggregation (per request) — cepat implementasi, tidak scalable for heavy filters; (B) Precompute nightly + incremental updates — recommended for production.
- Incremental strategy (recommended):
  - On F02/F03 create/update events, push `UpdateAggregatesForRecord` job to a lower-priority queue that updates aggregates for the affected periode/upp/aspek/indikator.
  - Full rebuild job (`RebuildAnalyticsAggregatesJob`) scheduled nightly or on demand.
  - For high-throughput writes, batch delta updates with a small window (e.g., group records per minute) to reduce contention.
  - Median: compute in job (PHP) and store in aggregates — do not compute median on-the-fly for large sets. Implementation detail:
    - Provide an explicit config `analytics.median_in_memory_threshold` (default `500_000`) that controls when the aggregator uses an in-memory PHP median (only when row count < threshold).
    - For large sets (>= threshold) use a SQL-window fallback (MySQL 8+). Example SQL to compute median safely:

```sql
WITH ordered AS (
  SELECT score, ROW_NUMBER() OVER (ORDER BY score) rn, COUNT(*) OVER () cnt
  FROM f02_responses
  WHERE periode_id = :periode_id AND is_latest_version = 1 /* plus filters */
)
SELECT AVG(score) AS median
FROM ordered
WHERE rn IN (FLOOR((cnt+1)/2), CEIL((cnt+1)/2));
```

    - The aggregator job should choose the method by counting rows first, then compute median with the chosen method. Tune `median_in_memory_threshold` with real memory profiles in staging.

7) Contoh query agregasi (performant)
- Per-aspek/indikator (MySQL):

```sql
SELECT
  f.aspek_id,
  f.indikator_id,
  COUNT(*) AS total_responses,
  AVG(f.score) AS avg_score,
  SUM(CASE WHEN f.validated = 1 THEN 1 ELSE 0 END) / COUNT(*) * 100 AS pct_validated
FROM f02_responses f
WHERE f.periode_id = :periode_id
  AND f.is_latest_version = 1
  /* optional filters: upp_id, opd_id, aspek_id */
GROUP BY f.aspek_id, f.indikator_id;
```

- Median (recommended in aggregator job using PHP):
  - Collect scores (or use window functions in MySQL 8+), compute median in job, store in `analytics_aggregates`.

8) API surface (kontrak singkat)
- GET /analytics
  - Params: periode_id, provinsi_id, opd_id, upp_id, aspek_id, indikator_id, validated, page, per_page
  - Response: { summary: {...}, charts: {...}, table: { rows: [...], meta: {...} } }
- GET /analytics/aspek
- GET /analytics/indikator
- GET /analytics/exports/csv?params... (creates or streams)
- POST /analytics/exports/pdf { params } -> 202 Accepted with export_id
- GET /analytics/exports/{id}/status
- GET /analytics/exports/{id}/download (signed)
- POST /analytics/exports/{id}/retry
- POST /analytics/reports/schedule

API versioning

- Decision: use URL versioning for public JSON endpoints, e.g. `/api/v1/analytics/...`. Implement routes under a versioned prefix (`api/v1`) so breaking changes can be introduced in later versions. Optionally support header-based version negotiation in the future, but start with URL versioning for simplicity and observability.

9) Export implementation (detail)
- Decision flow for CSV export:
  1. Controller validates params and enforces `Idempotency-Key` (rejects or deduplicates duplicate requests within configured idempotency window), then calls `AnalyticsExportService::estimateRows($params)`.
  2. If rows <= SYNC_THRESHOLD (configurable, default 50k), stream sync response.
  3. Else, create `analytics_exports` record (store `idempotency_key`, `correlation_id`, `params`) and dispatch `GenerateAnalyticsCsvJob`.
  4. Job writes to temp file (`storage/app/exports/analytics/{yyyy}/{mm}/{id}.csv`), sets `status=ready`, stores `file_size` and `processed_rows`.
  5. Dispatch `SendAnalyticsExportNotificationJob` with signed download URL and `correlation_id`.

- CSV streaming code pattern (controller):

```php
public function streamCsvResponse(array $params)
{
  $fileName = 'analytics_export_'.now()->format('Ymd_His').'.csv';
  $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=\"$fileName\"",];

  $callback = function() use ($params) {
    $handle = fopen('php://output', 'w');
    fputcsv($handle, ['periode_id','upp_id','upp_nama','aspek_id','aspek_nama','indikator_id','indikator_nama','total_responses','avg_score','pct_validated']);
    $query = $this->buildAggregateQuery($params);
    $query->chunkById(1000, function($rows) use ($handle) {
      foreach ($rows as $row) {
        fputcsv($handle, [...]);
      }
    });
    fclose($handle);
  };

  return response()->stream($callback, 200, $headers);
}
```

10) File storage & signed URLs
- Preferred: S3 (or S3-compatible) with `Storage::disk('s3')->temporaryUrl($path, now()->addHours(config('analytics.export_ttl_hours', 48)))` for signed downloads.
- Official fallback: local disk with a signed download route (`GET /analytics/exports/{id}/download`) protected by `signed` middleware and additional authorization checks against `analytics_exports` (user scope + role). Implement both code paths and select the active disk via `config('analytics.storage_disk')`.
  Ensure signed-route handlers stream files efficiently and validate `analytics_exports` record, `idempotency_key`, and `correlation_id` as part of authorization and observability.

11) Security & RBAC
- Use `spatie/laravel-permission` to define roles: `admin`, `analyst`, `viewer`.
- Middleware: `role:admin|analyst` on analytics routes; `tenant-scope` middleware to ensure users only see data for their OPD/desa.
- Policy: `AnalyticsExportPolicy` for `create`, `download`, `retry`, `manage_schedule`.
- Audit: every export action logs `user_id`, params, timestamp, and store in `analytics_exports` (do not expose raw params in download URL).

Authorization & Tenant Scoping (MANDATORY)
- All services, controllers, queued jobs, export flows, and aggregate rebuild processes MUST accept and propagate a single scoped context (`ScopeContext`) containing at minimum: `tenant_id`/`opd_id`, `scope_key`, `user_id`, `roles`, and `correlation_id`. No background worker or service should execute cross-tenant queries without an explicit, auditable exception.
- Implementation guidance:
  - Enforce scope checks in the service layer (`AnalyticsReadService`, `AnalyticsAggregationService`, `AnalyticsExportService`) so every read/write honors the provided `scope_key`/`opd_id`.
  - Provide job middleware (e.g., `EnsureScopeContext`) that rejects jobs lacking scope or where the scope is not authorized for the enqueued user/context. Implementation note: Laravel supports `Queue::createPayloadUsing()` and job middleware via the `middleware()` method (or global queue middleware) — use these to inject and persist a `ScopeContext` into analytics job payloads automatically so you don't need to add manual constructor parameters on every job class.
  - Persist `scope_key` and `tenant_id` on `analytics_exports` and any rebuild job records for traceability.
  - Enforce scope checks in the service layer (`AnalyticsService`, `AnalyticsAggregationService`, `AnalyticsExportService`) so every read/write honors the provided `scope_key`/`opd_id`.
  - Provide job middleware (e.g., `EnsureScopeContext`) that rejects jobs lacking scope or where the scope is not authorized for the enqueued user/context. Implementation note: Laravel supports `Queue::createPayloadUsing()` and job middleware via the `middleware()` method (or global queue middleware) — use these to inject and persist a `ScopeContext` into analytics job payloads automatically so you don't need to add manual constructor parameters on every job class.
  - Persist `scope_key` and `tenant_id` on `analytics_exports` and any rebuild job records for traceability.

  - `ScopeContext` value object (recommended): provide a sealed/value class to make serialization and validation explicit. Example (PHP):

```php
final class ScopeContext
{
    public function __construct(
        public readonly int $tenantId,
        public readonly string $scopeKey,
        public readonly int $userId,
        public readonly array $roles,
        public readonly string $correlationId,
    ) {}

    public static function fromRequest(Request $r): self { /* validate and map */ }
    public function toArray(): array { return [...]; }
    public static function fromArray(array $data): self { /* validate keys and types */ }
}
```

  Use `ScopeContext::toArray()` when serializing job payloads and `ScopeContext::fromArray()` in workers. Put validation in the value object so all consumers share the same contract.
- Download authorization: signed URLs are necessary but not sufficient. The download endpoint (`GET /analytics/exports/{id}/download`) MUST validate the calling user's authorization and tenant scope against the `analytics_exports` record before streaming the file.

Aggregates granularity & schema guidance
- Standard implementation: use a **single-table** `analytics_aggregates` that stores rows for multiple rollup levels (indicator/aspek/upp/opd/provinsi). Single-table provides consistent querying and simpler schema management. For extreme scale cases (per-period source rows > 1,000,000) a multi-table rollup strategy remains an acceptable optimization — document and justify when chosen.
- Required columns (core):
  - `periode_id` (unsignedBigInteger)
  - `tenant_id` (unsignedBigInteger) or `scope_key` (string) — tenant scoping (index)
  - `level` (enum: `indicator|aspek|upp|opd|provinsi`) — rollup level (index)
  - `dimension_hash` (string) — sha1 of a deterministic canonical representation of the dimension set + level (index). Recommended canonicalization and hardening:

    - Use an explicit, ordered `key:value` representation and a non-ambiguous separator (avoid plain `|` unless you escape values). Example (PHP):

```php
$parts = [
  'level'     => (string)$level,
  'periode'   => (string)$periode_id,
  'scope'     => (string)$scope_key,
  'upp'       => (string)$upp_id,
  'aspek'     => (string)$aspek_id,
  'indikator' => (string)$indikator_id,
];

// Use NUL as a separator to avoid ambiguity in values and include explicit key names
$canon = implode("\0", array_map(fn($k, $v) => "{$k}:{$v}", array_keys($parts), $parts));
$dimension_hash = sha1($canon);
```

    - Ensure missing/null values are represented deterministically (empty string). Prefer NUL (`"\0"`) as a separator or strong escaping when NUL is not possible. Avoid ambiguous concatenation that could be parsed two ways.

    - Persist the canonical component columns (`upp_id`, `aspek_id`, `indikator_id`, etc.) in their own columns (these exist in the schema) and ensure the aggregator writes the hash and the component columns atomically so they remain consistent. Add a unit test for the canonicalizer (determinism) and a small property-based test that checks for accidental collisions on random inputs.
  - `aggregate_version` (unsignedInteger) — increment to invalidate cache or indicate recompute
  - `upp_id`, `aspek_id`, `indikator_id` (as applicable)
  - metrics columns (`total_responses`, `avg_score`, `median_score`, etc.) and `computed_at`.
- Uniqueness & indexing: enforce `unique(['periode_id','scope_key','dimension_hash'])` (or use `tenant_id` if preferred) to guarantee a single canonical row per dimension set + level + periode. Use additional indexes on `periode_id`, `scope_key`, `level`, and `computed_at` for fast reads.
- Recommendation: single-table + `dimension_hash` is the default standard. Choose multi-table only when justified by measured scale/performance issues.
- Query guidance: prefer reading indicator-level aggregates for detail tables; KPI cards and charts should read appropriate rollups via `level` filters. Avoid ad-hoc SQL that mixes scoped rows from different levels without explicit `level` checks.

Data-layer contract (required)
- Create an explicit contract that maps each metric to its source of truth and allowed read path (aggregate vs live). Implemented as methods on `AnalyticsReadService` and enforced in code reviews.
- Example minimal mapping (document in `docs/ANALYTICS_METRICS.md` and reference here):
  - `total_submits`: source `f02_responses` with `is_latest_version = 1`. Read path: `analytics_aggregates` (indicator-level) for KPIs/charts; live only for drill-downs with scope limits.
  - `total_validated`: source `f02_responses(validated = 1)`. Read path: `analytics_aggregates`.
  - `avg_IPP`: source `f02_responses(score)` aggregated into `analytics_aggregates` (2 decimal rounding); do not compute per-request for charts.
  - `median`: computed by aggregator job (PHP/window function when feasible) and stored in aggregates; avoid on-the-fly median for large sets.
  - `distribusi`: histogram bins computed in aggregator; frontend accepts bin preset names.
- Read rules: methods that return KPI/chart data MUST accept `ScopeContext` and a flag `prefer_aggregates` (default true). Drill-down endpoints may use live queries but must also accept `ScopeContext` and enforce limits (max rows, rate limiting) when live reads are allowed.

Export rules reinforcement
- Idempotency is mandatory: clients must send `Idempotency-Key`. `AnalyticsExportService` must deduplicate using `idempotency_key` and return existing `analytics_exports` when within idempotency window.
- `correlation_id` (UUID) must be set at request time, persisted on `analytics_exports`, propagated to job payloads, and displayed in the exports UI/status pages so users and support staff can reference it.
- Export jobs MUST update progress fields on `analytics_exports` periodically (`processed_rows`, `total_rows_estimate`, `progress_percent`) — e.g., every N rows (default 1000) or every X seconds.
- Export retry/duplicate rules: if a duplicate request is detected via `idempotency_key`, do not queue a new job; return the existing export record and its status.
 - Idempotency is mandatory: clients must send `Idempotency-Key`. `AnalyticsExportService` must deduplicate using `idempotency_key` and return existing `analytics_exports` when within idempotency window.
 - `correlation_id` (UUID) must be set at request time, persisted on `analytics_exports`, propagated to job payloads, and displayed in the exports UI/status pages so users and support staff can reference it.
 - Export jobs MUST update progress fields on `analytics_exports` periodically (`processed_rows`, `total_rows_estimate`, `progress_percent`) — e.g., every N rows (default 1000) or every X seconds.
 - Export retry/duplicate rules: if a duplicate request is detected via `idempotency_key`, do not queue a new job; return the existing export record and its status.
 - Failure & retry semantics (explicit): when a request arrives with an existing `idempotency_key`:
   - If an existing export record has status `pending`, `processing`, or `ready`, return that record (no new job queued).
   - If the existing export record has status `failed`, respond with HTTP `409 Conflict` and include the `export_id` and `status` in the response; require the client to explicitly call `POST /analytics/exports/{id}/retry` to requeue the job. Optionally support an emergency header `X-Idempotency-Force-Retry: true` to requeue automatically and increment `idempotency_attempts` (implement cautiously).
   - Persist `idempotency_attempts` (unsignedInteger, default 0) and `last_attempted_at` (timestamp) on `analytics_exports` to help ops and rate-limits.
   - `POST /analytics/exports/{id}/retry` MUST validate the original `idempotency_key`, verify authorization, create a new processing attempt, and update `idempotency_attempts`.

  Export rate limiting (design)

  - Rationale: prevent a single user (or tenant) from flooding the `exports` queue with large queued jobs. Implement a minimal, practical rate limiter before implementing controllers.
  - Where to store counters: Redis is recommended for low-latency counters using keys with daily TTLs. Key patterns:
    - Per-user: `analytics:exports:rate:user:{user_id}:{YYYY-MM-DD}`
    - Per-tenant: `analytics:exports:rate:tenant:{tenant_id}:{YYYY-MM-DD}` (optional, recommended)
  - Atomic increment: use `INCR` and set `EXPIRE` to the midnight TTL on the first increment (or use a Lua script to ensure atomic set+expire). Redis is preferred over DB counters for performance.
  - Default policy (configurable in `config/analytics.php`):
    - `max_queued_exports_per_user_per_day` = 5
    - `max_queued_exports_per_tenant_per_day` = 100
    - `export_rate_limit_bypass_roles` = ['admin']
  - Enforcement point: `AnalyticsExportController::store()` (before creating `analytics_exports` record) should check both per-user and per-tenant counters; if either limit is exceeded, respond with HTTP `429 Too Many Requests` and include `Retry-After` (seconds until the quota window resets, e.g., seconds until midnight). Response body should include a short message and the configured limits.
  - When to increment/decrement:
    - Increment the counter atomically when you enqueue a queued export (status `pending`), not when the job starts processing. Do NOT decrement on failure — counts represent user-initiated attempts in the daily window. Track `idempotency_attempts` to limit retries.
  - Bypass: roles in `export_rate_limit_bypass_roles` bypass checks (e.g., `admin`). Make bypass configurable and auditable.
  - Alternative/opt-in: if you prefer durable counters, persist daily aggregates in a DB table `analytics_export_quota` updated via DB transactions, but accept higher latency and contention. Documented default is Redis.

CSV chunking & job-safety
- For CSV exports, prefer `chunkById` only when the underlying query has a stable, monotonic id. For aggregate-source exports, always ORDER deterministically (e.g., `ORDER BY dimension_hash`) and prefer `cursor()` or ordered chunking with a stable sort key to avoid missed or duplicated rows.
- All export jobs and aggregation jobs MUST accept and validate `ScopeContext` and must never perform unscoped full-table scans.

Service SLAs (targets)
- Dashboard load (cards + initial charts): < 3 seconds (from cache/precompute under normal load).
- Chart API (from cache/precompute): < 500 ms.
- Export sync: < 30 seconds for datasets <= `SYNC_THRESHOLD` (default 50k rows).
- Export queued: complete within < 2 hours for large datasets (configurable per env).
- Aggregate rebuild (per period): target < 30 minutes for a single period's full rebuild (tunable based on dataset size).

Monitoring thresholds (suggested defaults)
- Exports queue backlog: alert if `exports` queue backlog > 50 jobs for > 10 minutes.
- Job failure rate: alert if job failure rate > 5% over a 1-hour window.
- Export runtime SLA: alert if any export job runs > 2 hours (critical); raise warning if > 1 hour.
- Orphan files: alert if cleanup job finds > 10 orphan export files during a run.
- Throughput regression: alert if `analytics_export_rows_per_minute` drops > 50% vs baseline over 30 minutes.
- Store these numeric defaults in `config/analytics.php` and allow ops to tune per environment.
 - Monitoring thresholds (suggested defaults)
 - Exports queue backlog: alert if `exports` queue backlog > 50 jobs for > 10 minutes.
 - Job failure rate: alert if job failure rate > 5% over a 1-hour window.
 - Export runtime SLA: alert if any export job runs > 2 hours (critical); raise warning if > 1 hour.
 - Orphan files: alert if cleanup job finds > 10 orphan export files during a run.
 - Throughput regression: alert if `analytics_export_rows_per_minute` drops > 50% vs baseline over 30 minutes.
 - Aggregator stagnation: alert if `aggregate_version` (or `computed_at`) for a given `periode_id` + `scope_key` does not change for > 2 hours (configurable). This helps detect stalled incremental updaters or broken workers.
 - Store these numeric defaults in `config/analytics.php` and allow ops to tune per environment.

12) Monitoring, observability & ops
- Queue monitoring: Laravel Horizon (Redis). Configure supervisors per queue (`exports`, `aggregates`, `default`).
- Error monitoring: Sentry/Bugsnag.
- Metrics: record job durations, export sizes, and rows processed — push to Prometheus/Grafana if available.
- Alerts: queue backlog threshold, failed job rate > x%, export job duration > threshold.

13) Retention, cleanup & cost control
- Default export retention: 30 days (configurable). `CleanOldExportFilesJob` deletes files older than retention and marks records.
- Keep export logs (metadata) for 1 year for audit.

14) Testing & QA
- Unit tests: `AnalyticsService` calculations, `AnalyticsExportService::estimateRows`.
- Feature tests: endpoints with role-based access and pagination.
- Job tests: `GenerateAnalyticsCsvJob` writes to storage and updates DB (use `Storage::fake`).
- Load tests: simulate concurrent report generation and chart calls (k6/JMeter profile).
- Tenant-isolation & scope tests (required): add a dedicated test suite that asserts no cross-tenant leakage. Example PHPUnit feature test:

```php
public function test_analyst_cannot_access_other_tenant_analytics()
{
  $tenantA = Tenant::factory()->create();
  $tenantB = Tenant::factory()->create();
  // seed data for both tenants
  $userA = User::factory()->create(['tenant_id' => $tenantA->id]);

  $this->actingAs($userA)
     ->getJson('/api/analytics?periode=1')
     ->assertJsonMissing(['tenant_id' => $tenantB->id]);
}
```

Also add tests that validate job payload serialization/deserialization for `ScopeContext` and idempotency flows (retries and duplicate requests).

15) Risks & mitigasi (diperluas)
- Query overload / slow aggregation
  - Mitigasi: precompute aggregates, add indexes, throttle heavy endpoints, use read replicas if necessary.
- Export job failures / disk growth
  - Mitigasi: retention policy, quota alerts, retry/backoff, circuit breaker for exports.
- Data leaks across tenants
  - Mitigasi: strict tenant scoping in middleware + enforce in all queries and jobs; add automated tests for scoping.
- PDF layout fidelity
  - Mitigasi: use snappy/wkhtmltopdf or headless Chrome; add visual regression tests for sample reports.

16) Implementasi - rencana fase (full implementation)
- Phase 0 — Kickoff & infra readiness (1-2 hari)
  - Konfirmasi volume data, siapkan Redis, Horizon, S3/private storage, SSL, dan SMTP.
  - Define SYNC_THRESHOLD (default 50k rows).
- Phase 1 — Data & migrations (2 hari)
  - Migrations: `analytics_aggregates`, `analytics_exports`, `analytics_report_schedules`.
  - Models & basic seeder for roles.
- Phase 2 — Aggregation services & jobs (3 hari)
  - Implement `AnalyticsAggregationService`, `RebuildAnalyticsAggregatesJob`, incremental updater.
- Phase 3 — Export infra & jobs (3 hari)
  - Implement `AnalyticsExportService`, `GenerateAnalyticsCsvJob`, `GenerateAnalyticsPdfJob`, notification job.
- Phase 4 — Controllers, routes, policies (2 hari)
  - Implement APIs, policies, tenant middleware, and signed download route.
 - Phase 2 — Aggregation services & jobs (3 hari)
   - Implement `AnalyticsAggregationService`, `RebuildAnalyticsAggregatesJob`, incremental updater.
   - Add minimal controller/route skeletons and policy interfaces at the end of Phase 2 so export jobs and E2E flows can be exercised early (e.g., `/analytics/exports/*` status endpoints). This enables Phase 3 to be tested end-to-end without waiting for full frontend work.
 - Phase 3 — Export infra & jobs (3 hari)
   - Implement `AnalyticsExportService`, `GenerateAnalyticsCsvJob`, `GenerateAnalyticsPdfJob`, notification job. (Can run in parallel with Phase 4 once skeleton controllers exist.)
 - Phase 4 — Controllers, routes, policies (2 hari)
   - Implement APIs, policies, tenant middleware, and signed download route.
- Phase 5 — Frontend (Livewire) & charts (3-4 hari)
  - Livewire `AnalyticsPanel`, filters, ApexCharts integration, table with server-side pagination.
- Phase 6 — Scheduling, retention & ops (2 hari)
  - Scheduled reports, `CleanOldExportFilesJob`, monitoring setup.
- Phase 7 — Tests, hardening & docs (3 days)
  - Unit/feature/job tests, performance testing, runbook.

Estimasi total: 2–3 minggu (tim 1–2 pengembang + 1 ops/infra untuk konfigurasi Redis/S3/Horizon).

17) Deployment & migration checklist
- Add migrations and run `php artisan migrate` in deployment pipeline (run in maintenance window if needed).
- Seed roles & permissions.
- Deploy worker processes with Horizon supervisors for new queues.
- Run initial full `RebuildAnalyticsAggregatesJob` as a queued job; monitor duration.

18) Deliverables untuk diskusi eksternal
- Dokumen roadmap ini (versi ini) — ringkasan & detail teknis.
- Proposal perkiraan biaya infra (opsional) jika dibutuhkan storage/worker tambahan.
- Daftar asumsi yang perlu divalidasi saat presentasi.

19) Acceptance criteria (untuk review)
- Semua endpoint berfungsi dengan role-based access control.
- Export job berhasil menulis file, mengupdate `analytics_exports` dan menghasilkan signed download.
- Charts menampilkan data konsisten dengan `analytics_aggregates` (spot-check terhadap raw queries).
- Retention job menghapus file sesuai kebijakan.

20) Asumsi yang perlu dikunci saat diskusi
- Estimasi volume data per periode (paling penting).
- Pilihan engine PDF (snappy vs dompdf) berdasarkan contoh report.
- Storage: S3/public vs local — pengambilan keputusan mempengaruhi signed URL implementation.

21) Next step yang saya rekomendasikan
- Anda review dokumen ini, tambahkan catatan (asumsi & kebutuhan bisnis tambahan), dan konfirmasi pilihan infra (Redis/S3/Horizon).
- Jika setuju, saya akan:
  1. buat branch `feature/analytics-full` dan scaffold skeleton (migrations, controllers, services, jobs, Livewire component),
  2. commit dan buat PR untuk review internal.

Lampiran A — header CSV contoh

periode_id,periode_label,upp_id,upp_nama,aspek_id,aspek_nama,indikator_id,indikator_nama,total_responses,avg_score,median_score,pct_validated,pct_empty,last_response_at

Lampiran B — potongan kode contoh (CSV streaming) dan query agregasi
- Streaming CSV: lihat contoh di bagian 9.
- Query agregasi: lihat bagian 7.

---
Dokumen ini sudah menambahkan semua hasil analisis terbaru (filter `is_latest_version`, rekomendasi precompute, queue-based export, signed URLs, retention, monitoring, dan trade-off teknis). Silakan review; saya siap untuk membuat scaffold/PR setelah Anda konfirmasi keputusan infra dan prioritization.

Tambahan penting yang telah ditambahkan
- Saya menambahkan dokumen kamus metrik terpisah: [docs/ANALYTICS_METRICS.md](docs/ANALYTICS_METRICS.md) yang berisi definisi formal tiap metrik (sumber data, filter wajib, rumus, pembulatan, null handling, dan mode hitung).  
- Roadmap ini juga diperluas dengan: cache key strategy, idempotency untuk export, job progress fields (`processed_rows`, `total_rows_estimate`), correlation id untuk tracing, file naming & orphan cleanup rules, serta daftar metrik operasional untuk monitoring.

Operational & data-model enhancements (ringkas)
- `analytics_aggregates` core metadata (REQUIRED): `scope_key` (string) or `tenant_id` (unsignedBigInteger), `aggregate_version` (unsignedInteger), and `dimension_hash` (string). Optional metadata: `periode_label`, `last_source_updated_at`. These core columns enable safe scoping, deterministic uniqueness, and cache invalidation. If extreme compactness is required, a separate `analytics_aggregate_meta` table may be used, but the default implementation should include the core columns.
- `analytics_exports` diperluas dengan: `idempotency_key`, `correlation_id`, `processed_rows` (int), `total_rows_estimate` (int), `progress_percent` (decimal) — memudahkan UI progress dan idempotency checks.

Cache, idempotency & tracing
- Cache key pattern (contoh): `analytics:summary:v{aggregate_version}:{sha1(json_encode(filters))}`. Default TTL: 10 menit.
- Incremental update invalidation: aggregator incremental updates MUST increment `aggregate_version` for affected aggregate rows (recommended) — mis. lakukan `aggregate_version = aggregate_version + 1` pada baris yang terpengaruh — sehingga cache keys yang menyertakan `v{aggregate_version}` (mis. `analytics:summary:v{aggregate_version}:{sha1(json_encode(filters))}`) otomatis berubah dan cache lama dianggap usang. Jika tidak memungkinkan, lakukan targeted cache invalidation pada keys yang terpengaruh (mis. keys yang cocok `scope_key + periode_id + dimension_hash`). Pendekatan ini mendukung invalidasi sempit tanpa perlu full flush.
- Idempotency: require clients to provide an `Idempotency-Key` header on export requests. The server will compute a fallback key only when necessary, but production policy should mandate client-provided keys. Deduplicate or reject duplicate requests within a configurable idempotency window and persist `idempotency_key` on `analytics_exports`.
- Tracing: propagate `correlation_id` (UUID) from request into `analytics_exports`, queued job payloads, structured log context, and user-facing notifications; surface the correlation id in notification emails and the exports UI for troubleshooting.
- Cache key pattern (contoh): `analytics:summary:v{aggregate_version}:{sha1(json_encode(filters))}`. Default TTL: 10 menit.
- Incremental update invalidation: aggregator incremental updates MUST increment `aggregate_version` for affected aggregate rows (recommended) — mis. lakukan `aggregate_version = aggregate_version + 1` pada baris yang terpengaruh — sehingga cache keys yang menyertakan `v{aggregate_version}` (mis. `analytics:summary:v{aggregate_version}:{sha1(json_encode(filters))}`) otomatis berubah dan cache lama dianggap usang. Jika tidak memungkinkan, lakukan targeted cache invalidation pada keys yang terpengaruh (mis. keys yang cocok `scope_key + periode_id + dimension_hash`). Pendekatan ini mendukung invalidasi sempit tanpa perlu full flush.

- Race conditions & locking: incremental updater must guard against concurrent updates that can double-increment or skip versions. Two recommended patterns:
  - Optimistic update: read `aggregate_version` and then `UPDATE ... SET aggregate_version = aggregate_version + 1, ... WHERE id = :id AND aggregate_version = :expected_version`. If affected rows == 0, retry the read/compute step (bounded retries).
  - Pessimistic lock: when updating multiple dependent rows in a transaction, use `SELECT ... FOR UPDATE` to lock the aggregate rows before computing and writing results (works for transactional workloads and smaller batches).

  Example optimistic update (pseudo-SQL):

```sql
UPDATE analytics_aggregates
SET total_responses = :tr, avg_score = :avg, aggregate_version = aggregate_version + 1, computed_at = NOW()
WHERE id = :id AND aggregate_version = :expected_version;
```

  If affected_rows == 0 then re-read and retry (bounded attempts) to handle concurrency safely.
- Idempotency: require clients to provide an `Idempotency-Key` header on export requests. The server will compute a fallback key only when necessary, but production policy should mandate client-provided keys. Deduplicate or reject duplicate requests within a configurable idempotency window and persist `idempotency_key` on `analytics_exports`.
- Tracing: propagate `correlation_id` (UUID) from request into `analytics_exports`, queued job payloads, structured log context, and user-facing notifications; surface the correlation id in notification emails and the exports UI for troubleshooting.

Job progress, backoff & observability
- Jobs update `analytics_exports` with `processed_rows`, `total_rows_estimate`, and `progress_percent` periodically (e.g., every 1000 rows). Jobs should emit events for start/complete/failure and send minimal metrics (duration, rows processed).
- Backoff / retry policy: `$tries = 5`, backoff exponential [60,300,900] seconds; configure circuit-breaker to pause new exports on repeated failures.

Storage, file naming & cleanup
- File path template: `exports/analytics/{yyyy}/{mm}/{uuidv4}.{ext}`. Use UUID for filename and store original metadata in `analytics_exports`.
- Signed URL policy: S3 `temporaryUrl(..., now()->addHours(config('analytics.export_ttl_hours', 48)))` or local signed route with `signed` middleware.
- Cleanup: `CleanOldExportFilesJob` deletes files older than retention and marks records; also run a reconcile job to delete orphan files and mark failed exports.

Monitoring & operational metrics (to emit)
- `analytics_export_duration_seconds` (histogram)
- `analytics_export_rows_total` (counter)
- `analytics_export_failure_rate` (ratio)
- `analytics_job_wait_seconds` (gauge) — job wait time (time in queue before execution)
- `analytics_job_runtime_seconds{queue}` (histogram) — average runtime per queue/job type
- `analytics_job_failure_rate{job_type}` (ratio) — failure rate per job type
- `analytics_export_rows_per_minute` (gauge) — export throughput
- `analytics_export_orphan_files_total` (gauge) — number of orphan files discovered by cleanup/reconcile job
- `analytics_job_retries_total` (counter)
- Queue backlog alerts (per queue): `exports`, `aggregates` thresholds; alert on sustained backlog or rising failure rates.

UX & drill-down rules
- Chart click applies filter and updates detail panel (do not open modal by default). Provide breadcrumb of active filters and a `Reset drill-down` control.

Frontend design choices (recommended):

- Data fetching: fetch heavy chart and table data from JSON endpoints (`/api/analytics/*`) so chart libraries receive raw JSON; use Livewire/Alpine for UI state, controls, and lightweight interactions but avoid rendering big datasets via Livewire payloads.
- Filter state: persist full filter state to the URL query string (pushState) so views are shareable and browser back/forward work. Normalize filters (sorted keys) before serializing to avoid cache misses.
- Loading UX: render independent skeleton/loaders per KPI card, per chart, and per table so slow components don't block the whole page. Use incremental hydration — show KPI cards first (fast), then charts, then table.
- Drill-down behavior: clicking chart series applies filters, updates detail panel and pushes state to history. Render a compact breadcrumb of active filters. Support `Reset drill-down` and `Copy link` for sharing the current state.
- Wireframes & state machine: before Phase 5, add a small UI state machine (filters → apply → loading → results → drill-down) and 1–2 wireframes that show where skeletons, charts, table and breadcrumbs appear. This will prevent integration rework during frontend implementation.

- Histogram bins defined server-side as presets; frontend accepts presets by name (e.g., `default_5bins`, `fine_10bins`).

- **Keputusan Eksplisit & Fallback (Standar Implementasi)**
- **Volume bucket default:** Untuk implementasi awal anggap `<100k` rows per periode. Jika monitoring menunjukkan >1,000,000 rows per periode, aktifkan partitioning dan strategi rolling aggregates/read-replica.
- **Storage & signed URL:** Default preferensi: S3 (or S3-compatible) menggunakan `Storage::disk('s3')->temporaryUrl(...)`. Fallback resmi: local disk dengan signed download route (`signed` middleware). Pilih via `config('analytics.storage_disk')`.
- **PDF engine:** Default `spatie/browsershot` (headless Chromium) for fidelity and maintenance. Fallback: `dompdf` for very simple templated reports. Notes: headless Chromium requires a maintained Chromium binary (use official Chromium Docker images or include the binary in worker images); document recommended Docker images and health-checks for the binary.
- **SYNC_THRESHOLD:** Default `50_000` rows (`config('analytics.sync_threshold')`).
- **Idempotency:** Export requests wajib menyertakan `Idempotency-Key`. Server menghitung fallback hanya bila perlu, namun kebijakan produksi harus mensyaratkan key dari client.
- **File naming & folder path:** Gunakan UUID untuk nama file internal, path `exports/analytics/{YYYY}/{MM}/{uuid}.{ext}`. Simpan metadata dan `idempotency_key` di `analytics_exports`.
- **Cache strategy (hybrid):** KPI cards & utama charts baca dari precompute (`analytics_aggregates`) + cache; drill-down melakukan live query atau membaca dari aggregate-detail table sesuai kebutuhan. Cache TTL default 10 menit; invalidasi dilakukan dengan menaikkan `aggregate_version` atau saat incremental update target.
- **Partitioning rule:** Terapkan partitioning (per periode/rolling) ketika per-period source rows > 1,000,000 atau saat query latency melampaui SLA.
- **CSV chunk caution:** Saat menggunakan `chunkById`, pastikan query memiliki kolom `id` monotonic dan stabil; jika tidak, gunakan `cursor()` atau ordered chunking untuk menghindari rows skip/duplicate.
- **Correlation ID:** Propagasikan `correlation_id` (UUID) ke `analytics_exports`, payload job, log structured context, dan notifikasi; tampilkan correlation id pada email/UI untuk troubleshooting.

Decisions to be made (butuh konfirmasi)
1. Volume bucket: berapa rata-rata baris `F02` per periode? (<100k / 100k–1M / >1M) — mempengaruhi partitioning & precompute frequency.
2. Storage: `S3` (recommended) atau local disk? (S3 memberi temporaryUrl, local butuh signed route).
3. PDF engine: `snappy/wkhtmltopdf` (recommended) vs `dompdf` vs headless Chrome — pilih berdasarkan contoh report fidelity.
4. SYNC_THRESHOLD: nilai default 50k — setuju atau ubah? (mis. 20k/50k/100k)
5. Retention policy untuk exported files (default 30 hari) — setuju atau perlu lebih pendek/panjang?
6. Cache TTL default & apakah caching per-tenant diperlukan? (default TTL 10 menit)
7. Precompute cadence: nightly full rebuild + incremental on write? (rekomendasi: incremental + nightly full)
8. Partitioning strategy: implement sekarang bila volume besar, atau tunda hingga crossing threshold?
9. Aggregate metadata location: simpan fields tambahan di `analytics_aggregates` atau buat `analytics_aggregate_meta` terpisah?
10. Idempotency key policy: accept client-sent header or server computed only?
11. Export rate limiting per user (e.g., max N queued exports per day) — numeric policy?
12. Notification channel: email only or in-app + email?
13. Read-replica usage for heavy live queries — tersedia atau tidak?
14. Role mapping: which exact roles have `Analisis` menu and export rights? (`admin`, `analyst`, additional?)
15. Acceptance SLAs for exports (e.g., sync <30s for small sets, queued <2h for large sets)

Pertanyaan operasional untuk diskusi singkat
- Apakah Redis sudah tersedia untuk queue/cache di environment produksi?  
- Apakah ada S3 (or compatible) bucket tersedia dan siapa yang akan mengelola credential?  
- Apakah tim ops setuju menggunakan Horizon untuk queue monitoring?  
- Siapa contact person untuk konfirmasi volume data F02 per periode?

Next steps yang saya kerjakan setelah keputusan
1. Setelah Anda konfirmasi keputusan di atas, saya akan update roadmap lagi, buat migration draft (termasuk kolom metadata), dan scaffold code dasar di branch `feature/analytics-full` (migrations, models, controllers, services, jobs, Livewire skeleton).  
2. Saya juga sudah membuat dokumen kamus metrik (`docs/ANALYTICS_METRICS.md`) — silakan cek dan beri masukan.

Prasyarat sebelum scaffold (TERKINI)

- `analytics_exports` migration example telah diperbarui di dokumen ini untuk menyertakan `tenant_id`, `scope_key`, `idempotency_key`, `correlation_id`, `processed_rows`, `total_rows_estimate`, `progress_percent`, `idempotency_attempts`, dan `last_attempted_at` — pastikan migration di codebase mengikuti contoh ini.
- Kontrak baca sudah dikunci pada `AnalyticsReadService` (lihat bagian Services & Data-layer contract) untuk menghindari duplikasi tanggung jawab antara kelas servis.
- Minimal export rate-limiting design ditambahkan (Redis counters, defaults, controller enforcement). Jika Anda ingin men-defer implementasi rate-limiting, nyatakan secara eksplisit; saat ini roadmap menetapkan implementasi minimal yang harus ada sebelum controller dibuat.

---
Silakan review perubahan ini. Untuk mempercepat diskusi publik, saya sarankan kita kunci 3 keputusan pertama (volume bucket, storage, PDF engine) pada pertemuan berikutnya.
