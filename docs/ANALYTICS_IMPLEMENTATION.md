# Analytics Implementation Notes

This document contains implementation and run instructions for the `feature/analytics-full` work.

Quick setup:

- Run migrations: `php artisan migrate`
- Seed roles (if using Spatie permissions): `php artisan db:seed --class=AnalyticsRolesSeeder`
- Start queue worker: `php artisan horizon` (recommended) or `php artisan queue:work --queue=exports,default,aggregates`

Files added in this feature branch:
- `app/Services/Analytics/*` — service classes
- `app/Jobs/Analytics/*` — queued jobs for CSV/PDF and aggregation
- `app/Models/Analytics*` — models & migrations
- `resources/views/livewire/analytics/panel.blade.php` — Livewire panel stub

Metrics hooks:
- `app/Services/Analytics/Metrics/AnalyticsMetrics.php` is a pluggable adapter that currently logs metric events. Replace with a Prometheus or StatsD client in production.

Horizon & Chromium notes: see `docs/HORIZON_SETUP.md` for recommended Horizon supervisor config and browsershot sidecar guidance.
