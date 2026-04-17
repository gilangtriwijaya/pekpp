<?php

return [
    // threshold for streaming vs queued export
    'sync_threshold' => env('ANALYTICS_SYNC_THRESHOLD', 50000),

    // exported files retention (days)
    'export_retention_days' => env('ANALYTICS_EXPORT_RETENTION_DAYS', 30),

    // pdf engine: snappy|dompdf|chrome
    'pdf_engine' => env('ANALYTICS_PDF_ENGINE', 'snappy'),

    // storage disk for exports: s3|local
    'storage_disk' => env('ANALYTICS_STORAGE_DISK', 'local'),

    // signed url lifetime for exports (hours)
    'export_ttl_hours' => env('ANALYTICS_EXPORT_TTL_HOURS', 48),

    // idempotency window for export requests (minutes)
    'idempotency_window_minutes' => env('ANALYTICS_IDEMPOTENCY_WINDOW_MINUTES', 60),

    // cache ttl for analytics responses (minutes)
    'cache_ttl_minutes' => env('ANALYTICS_CACHE_TTL_MINUTES', 10),
];
