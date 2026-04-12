# users vs user_upp (authoritative guide)

- `users` = identity (SSO mirror)
  - Purpose: store SSO identity data (sso_user_id, nama, email, nip, role_sso, aktif, last_sync_at)
  - Not for authorization decisions
  - Synced from SSO only (no manual grants)

- `user_upp` = authorization (single source of truth)
  - Purpose: assign a `user` to a `upp` with a `peran` (enum)
  - Used at runtime to determine menus, actions, validation, and configuration rights
  - Populated by migration from legacy `user_unit_roles` initially, then managed by admins (superadmin/admin_organisasi)

Developer checklist

- At login: update `users` (mirror), then load `user_upp` rows for the `users.id` to build permissions.
- Never read `role_sso` or `users` to decide app permissions.
- All controllers and middleware must consult `user_upp` (or centralized authorization service) for access checks.
- `user_unit_roles` is archived as `user_unit_roles_legacy` — use exported CSV for reconciliation if needed.

Runtime enforcement

1. Register middleware `App\Http\Middleware\EnsureUserUpp` on authenticated routes so `userUpps` are eager-loaded.

Example (routes/web.php):

```php
use App\Http\Middleware\EnsureUserUpp;

Route::middleware(['auth', EnsureUserUpp::class])->group(function () {
  // protected routes here
});
```

2. Use the `App\Services\UserPermission` helper to centralize checks:

```php
$perm = app(\App\Services\UserPermission::class);
if ($perm->hasRole(auth()->user(), $uppId, 'admin_upp')) {
  // allow
}
```

3. Blade/UI: prefer `auth()->user()->getUserUpps()` or `role_sso` for display only. All authorization must consult `user_upp`.
