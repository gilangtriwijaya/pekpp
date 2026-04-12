# SSO Integration & Secret Management (PEKPP)

Ringkasan implementasi dan petunjuk operasi untuk integrasi SSO (pull API).

## Gambaran singkat
- Tipe autentikasi pull yang didukung:
  - Bearer token: `SSO_PULL_TOKENS` (comma-separated, client uses first entry)
  - HMAC signing: `SSO_PULL_SECRETS` (comma-separated, client uses first entry)
- Server SSO menerima banyak token/secret untuk rotasi aman.

## Lokasi file & konfigurasi
- Aplikasi: `apps/pekpp`
- SSO app: `apps/sistagor-sso`
- PEKPP `.env` variabel terkait: `SSO_BASE_URL`, `SSO_PULL_TOKENS`, `SSO_PULL_SECRETS`, `SSO_APP_CODE`
- SSO `.env` variabel terkait: `SSO_PULL_TOKENS`, `SSO_PULL_SECRETS`, `SSO_PULL_SIGNATURE_MAX_SKEW`

## Cara kerja
- Klien (`app/Services/SsoClient.php`) memilih HMAC bila `SSO_PULL_SECRETS` diset; jika tidak, pakai bearer token.
- HMAC flow: klien mengirim header `X-SSO-Timestamp` (unix) dan `X-SSO-Signature` (sha256 hex) atas string `<timestamp>.<body>`.
- Server memeriksa signature terhadap semua secret yang dikonfigurasikan (`SSO_PULL_SECRETS`) sehingga rotasi dapat dilakukan tanpa gangguan.

## Rotasi token/secret (manual)
1. Buat nilai baru: `openssl rand -hex 32`.
2. Tambahkan nilai baru ke SSO `.env` pada `SSO_PULL_TOKENS` (prefix baru, atau tambahkan memakai koma di depan) atau `SSO_PULL_SECRETS`.
3. Tambahkan nilai baru ke file env pada client (mis. `/etc/pekpp/secret` atau `apps/pekpp/.env`).
4. Clear caches pada kedua aplikasi:
   ```bash
   cd /home/deploy/apps/sistagor-sso && php artisan config:clear && php artisan cache:clear
   cd /home/deploy/apps/pekpp && php artisan config:clear && php artisan cache:clear
   ```
5. Setelah verifikasi pull sukses, hapus nilai lama dari daftar (SSO, lalu client) pada window transisi yang singkat.

## Menyimpan secrets pada VM tanpa Vault
Opsi aman lokal (rekomendasi):

- Simpan file secrets di `/etc/<app>/secret` (contoh: `/etc/pekpp/secret`) dengan perms ketat (owner `root`, group `deploy`, mode `640`).
- Pastikan file tidak masuk ke git dan tidak dibackup ke lokasi publik.
- Buat PHP-FPM pool atau systemd unit agar environment memuat file ini (export atau `EnvironmentFile=` pada unit/systemd).

Contoh pembuatan file (ops manual):
```bash
sudo mkdir -p /etc/pekpp
sudo bash -c 'cat > /etc/pekpp/secret <<EOF
SSO_PULL_TOKENS=...,...
SSO_PULL_SECRETS=...,...
EOF'
sudo chown root:deploy /etc/pekpp/secret
sudo chmod 640 /etc/pekpp/secret
```

## Scheduler
- Kernel added: `app/Console/Kernel.php` schedules `php artisan sso:mirror-users --chunk=500` daily at `03:10` with `withoutOverlapping()`.
- Cron file installed: `/etc/cron.d/pekpp-schedule` runs Laravel scheduler every minute and logs to `/home/deploy/apps/pekpp/storage/logs/schedule.log`.

## Moving secrets to `/etc` (what we did)
- For PEKPP we moved `SSO_PULL_TOKENS` and `SSO_PULL_SECRETS` out of `apps/pekpp/.env` into `/etc/pekpp/secret` (owner `root`, group `deploy`, mode `640`).
- A systemd drop-in at `/etc/systemd/system/php-fpm.service.d/pekpp-env.conf` was added with:

```
[Service]
EnvironmentFile=/etc/pekpp/secret
```

This exposes the secret values into the PHP-FPM process environment so Laravel reads them via `env()`.

Backup: original `apps/pekpp/.env` was copied to `apps/pekpp/.env.backup-moved-secrets` and moved variables were commented out in the active `.env`.

## Troubleshooting
- Jika 401: periksa token/header dan `SSO_PULL_ALLOWLIST` pada SSO.
- Jika 422: pastikan parameter `app` dikirim (client otomatis menambahkan `app=SSO_APP_CODE`).
- Audit logs: SSO menyimpan entri di table `sso_pull_audit_logs` (lihat header mask dan IP).

## Commands (quick)
- Manual dry-run mirror on PEKPP:
  ```bash
  cd /home/deploy/apps/pekpp
  php artisan config:clear
  php artisan sso:mirror-users --chunk=100
  ```

---
Catatan: dokumentasi ini akan diperbarui setiap ada fitur baru.
