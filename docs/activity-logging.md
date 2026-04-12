# Activity Logging (Audit)

This document describes the activity logging feature: what is recorded, where logs are stored, and how to query them.

Overview
- All web controller actions are recorded to the `activity_logs` table.
- The following events are captured:
  - Authentication events (login, logout)
  - Page/menu access (every controller route request)
  - CRUD requests routed through controllers
  - Misc actions (custom calls can write a log via `ActivityLog::record()`)

Table
- `activity_logs` columns:
  - `id` (bigint)
  - `user_id` (nullable)
  - `action` (string) — short action name or HTTP method + route
  - `route` (nullable) — named route
  - `method` — HTTP method
  - `path` — request path
  - `params` — JSON payload or query (sensitive fields excluded)
  - `ip` — request IP
  - `user_agent` — user agent string
  - `created_at` / `updated_at`

How it works
- Middleware `App\Http\Middleware\LogActivity` runs for controller routes and writes an entry after requests finish. The middleware excludes static asset paths (`css/*`, `js/*`, `images/*`, `favicon.ico`).
- The base controller (`App\Http\Controllers\Controller`) registers the middleware so controller actions are audited by default.
- The `App\Models\ActivityLog` model contains a helper `ActivityLog::record($action, $details = [])` for ad-hoc logging from controllers or services.

Examples
- Record an action from anywhere (controller/service):

```php
use App\Models\ActivityLog;

ActivityLog::record('user_import', ['params' => ['count' => 42]]);
```

- Query recent logins:

```php
use App\Models\ActivityLog;

$recentLogins = ActivityLog::where('action', 'like', 'login%')
    ->orderBy('created_at', 'desc')
    ->limit(50)->get();
```

Operational notes
- The migration file is at `database/migrations/*create_activity_logs_table.php`.
- Run `php artisan migrate` to create the table on your environment.
- We intentionally avoid storing sensitive fields. Middleware removes `_token`, `password`, and `password_confirmation` from saved params.
- If you need to exclude specific routes from logging, add them to the middleware exclusion list or add route middleware to opt-out.

Extending
- To log model-level events (attribute changes) consider adding an Eloquent observer that calls `ActivityLog::record()` with `created/updated/deleted` detail.
