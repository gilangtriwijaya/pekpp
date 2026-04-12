# PEKPP Feature Log

Chronological log of features and changes applied to the PEKPP app. Maintain this file: add one-line entries with date, author, and short description for every new feature or infra change.

- 2026-01-16 — assistant — Scaffold project, database, initial migrations and models.
- 2026-01-16 — assistant — Add `SsoClient`, `UserSyncService`, `MirrorUsersFromSso` command to mirror SSO users/opds/opd_units.
- 2026-01-16 — assistant — Add migrations for OPD, OPD units, SSO mappings, and SSO fields on `users`.
- 2026-01-16 — assistant — Provision `SSO_PULL_TOKEN` (temporary) and run initial dry-run (401 until token provisioned).
- 2026-01-16 — assistant — Implement HMAC support: `SSO_PULL_SECRETS` and client-side signing logic.
- 2026-01-16 — assistant — Implement multi-key acceptance on SSO (`SSO_PULL_TOKENS`, `SSO_PULL_SECRETS`) for rotation.
- 2026-01-16 — assistant — Add scheduler `app/Console/Kernel.php` and install `/etc/cron.d/pekpp-schedule` for Laravel scheduler.
- 2026-01-16 — assistant — Documented SSO integration and secret management in `docs/SSO-and-secret-management.md`.
- 2026-01-16 — assistant — Move PEKPP pull secrets from `apps/pekpp/.env` to `/etc/pekpp/secret`, configure systemd drop-in to load into PHP-FPM, and backup/comment old env entries.

Future entries: always append with date and short summary.
