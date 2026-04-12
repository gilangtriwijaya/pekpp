# UPP (Unit Pelayanan Publik) — dokumentasi singkat

Deskripsi singkat
- `UPP` adalah entitas evaluasi utama di PEKPP yang merepresentasikan seluruh unit penyelenggara pelayanan publik
  (baik organisasi induk maupun unit turunannya) yang disetarakan pada satu level penilaian.

Tujuan
- Menyimpan entitas evaluasi independen dari struktur organisasi SSO.
- Menyediakan referensi ke data SSO (`opd` / `opd_unit`) tanpa bergantung padanya.
- Mendukung hierarki `parent_upp_id` untuk pelaporan dan agregasi.

Lokasi berkas penting
- Migration: `apps/pekpp/database/migrations/2026_01_18_000001_create_upps_table.php`
- Model: `apps/pekpp/app/Models/Upp.php`
- Command sinkronisasi: `apps/pekpp/app/Console/Commands/SyncUppsFromOpd.php`
- Scheduler: `apps/pekpp/app/Console/Kernel.php` (jadwal `upp:sync-from-opd` hourly)

Struktur kolom (intinya)
- `id` (PK)
- `sso_opd_id` (nullable) — referensi `opds.sso_id` dari sumber SSO
- `sso_opd_unit_id` (nullable) — referensi `opd_units.sso_id` dari SSO
- `code` (nullable) — kode internal optional
- `nama` — nama UPP
- `slug` (nullable)
- `parent_upp_id` (nullable) — FK ke `upps.id` (ON DELETE SET NULL)
- `alamat`, `telepon` (opsional)
- `status` — enum: `AKTIF` / `NONAKTIF`
- audit: `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`

Catatan mapping & perilaku
- Sumber data utama untuk pengisian adalah tabel `opds` dan `opd_units` (yang dimirror dari SSO ke local DB).
- Sinkronisasi dibuat oleh command `upp:sync-from-opd` yang dijadwalkan hourly.
- `sso_opd_id` dan `sso_opd_unit_id` hanya referensi; struktur evaluasi tidak bergantung pada hirarki SSO.
- `parent_upp_id` digunakan untuk pelaporan/aggregasi di PEKPP.

Cara manual & debugging
- Jalankan sinkronisasi manual:

  cd /home/deploy/apps/pekpp
  php artisan upp:sync-from-opd

- Tampilkan data UPP (contoh):

  mysql -h localhost -P3306 -u pekpp -p'Pekpp@2026' pekpp -e "SELECT id,sso_opd_id,sso_opd_unit_id,code,nama,parent_upp_id,status,created_at FROM upps ORDER BY id LIMIT 200;"

- Periksa jadwal: crontab user yang menjalankan scheduler harus punya entry:

  * * * * cd /home/deploy/apps/pekpp && php artisan schedule:run >> /home/deploy/apps/pekpp/storage/logs/schedule.log 2>&1

- Lihat log scheduler:

  tail -n 200 /home/deploy/apps/pekpp/storage/logs/schedule.log

Perubahan yang dibuat oleh tim (lokasi file)
- Migration dan Model sudah ditambahkan di `apps/pekpp`.

Jika mau, saya bisa menambahkan:
- unit tests kecil untuk command sinkronisasi,
- logging/metrics untuk jumlah UPP baru yang dibuat/diperbarui saat sinkronisasi,
- endpoint API read-only untuk menelusuri UPP.
