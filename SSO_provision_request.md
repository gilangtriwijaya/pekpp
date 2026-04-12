# SSO Provisioning Request: PEKPP

Purpose: request SSO operator to provision a read-only pull token (or HMAC secret) so PEKPP can pull users/OPD/unit data via the SSO pull API.

Requested Vault path:

- `secret/apps/pekpp/SSO_PULL_TOKEN` (preferred: bearer token)
- or `secret/apps/pekpp/SSO_PULL_SECRET` (HMAC) if HMAC signing is required by policy

Required scopes / notes for token:

- Read-only access to the SSO pull endpoints: `/api/sso/users`, `/api/sso/opds`, `/api/sso/opd-units`.
- Expiration: ideally short-lived or renewable; please note token lifecycle.
- If possible, restrict token scope to `app=pekpp` pulls or IP allowlist the application host (optional but recommended).

After provisioning:

- Provide the Vault request ID (e.g. `VAULT_REQ_ID_20260116_XXXXX`) in the secure channel.
- Admin on PEKPP will run:

```bash
export SSO_PULL_TOKEN="$(vault kv get -field=token secret/apps/pekpp/SSO_PULL_TOKEN)"
php artisan config:clear
php artisan sso:mirror-users --chunk=100
```

Security notes:

- Do NOT send the token value in chat or ticket text. Use Vault or a secure channel.
- Prefer storing the token in the server's secret manager (Vault), not in plain `.env`.

If you want, I can also create an initial test token locally for trial (not recommended for production).