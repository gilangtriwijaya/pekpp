# Activity Logs — Implementation Notes

Documenting the Activity Logs feature and recent refactorings so future developers can understand, maintain, and extend it.

**Summary:**
- Implemented request/activity logging into a dedicated `activity_logs` table.
- Added middleware to record requests, an Eloquent model, admin UI (list + modal detail), routes, and a migration.
- Moved controller and views out of the `Admin` subfolder to top-level locations and removed old admin files.

**Files added / changed**
- Controller: [app/Http/Controllers/ActivityLogController.php](app/Http/Controllers/ActivityLogController.php)
- Views: [resources/views/activity_logs/index.blade.php](resources/views/activity_logs/index.blade.php) and [resources/views/activity_logs/show.blade.php](resources/views/activity_logs/show.blade.php)
- Model: [app/Models/ActivityLog.php](app/Models/ActivityLog.php)
- Middleware: [app/Http/Middleware/LogActivity.php](app/Http/Middleware/LogActivity.php)
- Routes: updated in [routes/web.php](routes/web.php) to use `ActivityLogController` (top-level)
- Migration: `database/migrations/2026_01_17_120000_create_activity_logs_table.php` (created and migrated)

**Routes**
- `GET /activity-logs` → `ActivityLogController@index` (list + filters)
- `GET /activity-logs/{id}` → `ActivityLogController@show` (returns JSON for modal)

**Behavior / Notes**
- The `index` supports filters: `q` (search), `user` (user id), `action` (string match), `start`/`end` (date range).
- Date range is normalized server-side (swaps start/end if start > end).
- The `show` method returns a JSON payload used to populate the modal detail UI.
- The middleware excludes the activity-logs routes themselves to avoid self-noise.

**Admin UI**
- The list view is flush with the sidebar/topbar and uses a single-line filter bar (search, user dropdown, action, date range, Reset/Search).
- The `user` dropdown is populated only with users present in `activity_logs` (distinct `user_id`s) to keep the list relevant.

**Maintenance / Developer tasks**
- Clear compiled views after changes: `php artisan view:clear`.
- If deploying to servers where files were root-owned, ensure `chown -R www-data:www-data storage bootstrap/cache resources/views` (adjust user/group as needed).
- To add authorization for viewing logs, protect routes with a role middleware (Spatie permissions already available in repo).

**Next improvements (optional)**
- Add CSV export for filtered results.
- Replace native date inputs with a JS date picker for convenience.
- Add role-based access (e.g., `permission:activity_logs.view`) and restrict UI accordingly.

**Author / Date**
- Implemented and documented by developer automation on 2026-01-17.
