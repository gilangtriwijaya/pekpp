<?php

return [
    // Storage disk for exports: 's3' or 'local'
    'storage_disk' => env('ANALYTICS_STORAGE_DISK', 'local'),

    // threshold for streaming vs queued export (number of rows)
    'sync_threshold' => (int) env('ANALYTICS_SYNC_THRESHOLD', 50000),

    // exported files retention (days)
    'export_retention_days' => (int) env('ANALYTICS_EXPORT_RETENTION_DAYS', 30),

    // signed url lifetime for exports (hours)
    'export_ttl_hours' => (int) env('ANALYTICS_EXPORT_TTL_HOURS', 48),

    // median computation in-memory threshold
    'median_in_memory_threshold' => (int) env('ANALYTICS_MEDIAN_IN_MEMORY_THRESHOLD', 500000),

    // pdf engine: 'snappy'|'dompdf'|'chrome'
    'pdf_engine' => env('ANALYTICS_PDF_ENGINE', 'snappy'),

    // idempotency window for export requests (minutes)
    'idempotency_window_minutes' => (int) env('ANALYTICS_IDEMPOTENCY_WINDOW_MINUTES', 60),

    // cache ttl for analytics responses (minutes)
    'cache_ttl_minutes' => (int) env('ANALYTICS_CACHE_TTL_MINUTES', 10),

    // Rate-limits (defaults)
    'rate_limits' => [
        'per_user_per_day' => (int) env('ANALYTICS_EXPORT_RATE_PER_USER', 5),
        'per_tenant_per_day' => (int) env('ANALYTICS_EXPORT_RATE_PER_TENANT', 100),
        'bypass_roles' => array_map('trim', explode(',', env('ANALYTICS_RATE_BYPASS_ROLES', 'admin'))),
    ],

    // How often jobs update progress (rows)
    'progress_update_every_rows' => (int) env('ANALYTICS_PROGRESS_UPDATE_EVERY_ROWS', 1000),
];
