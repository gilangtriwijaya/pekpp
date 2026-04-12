Panduan Pengisian Tabel user_upp (RESMI)

Tujuan tabel user_upp

`user_upp` adalah satu-satunya sumber kebenaran (single source of truth) untuk:

- hak akses user ke suatu UPP
- peran user dalam konteks UPP tertentu

Jika ada konflik: yang dipercaya selalu `user_upp`, bukan `users`, bukan SSO, bukan tabel legacy.

1. Kapan `user_upp` Diisi?
A. Saat sinkronisasi awal (migration / seeding)

Dilakukan sekali ketika:

- sistem pertama kali aktif
- atau saat migrasi dari sistem lama (`user_unit_roles`)

➡️ Sumber: tabel legacy + mirror SSO
➡️ Mode: semi-otomatis (script)

B. Saat ada perubahan organisasi / penugasan

Contoh:

- admin UPP diganti
- verifikator bertambah
- user dipindah unit

➡️ Sumber: input aplikasi (UI Admin)
➡️ Mode: manual, terkontrol

C. Saat user baru login via SSO

Yang dilakukan sistem:

✔ tambah ke `users` (mirror)

❌ tidak otomatis masuk ke `user_upp`

➡️ User belum punya akses apa pun sampai: ditetapkan ke UPP via `user_upp`

Ini disengaja demi keamanan.

2. Isi Tiap Kolom `user_upp` (WAJIB & SUMBERNYA)
```
user_id         : Isi dari users.id (hasil login SSO mirror)
upp_id          : Isi dari upps.id (master UPP)
peran           : salah satu enum resmi (lihat daftar di bawah)
aktif           : boolean (true = masih berlaku, false = nonaktif)
ditetapkan_oleh : users.id admin yang menetapkan (NULL jika migrasi)
ditetapkan_pada : timestamp penetapan (boleh use created_at untuk migrasi)
```

Catatan penting:
- Jangan pakai `sso_user_id` langsung di `user_upp`.
- UPP ≠ user; UPP = objek kerja/penilaian.

Peran resmi (enum):

| Nilai ENUM | Makna |
|------------|-------|
| superadmin | Akses global |
| admin_organisasi | Kelola UPP & penugasan |
| admin_upp | Isi F01, kelola internal UPP |
| verifikator | Validasi F02 |

❗ Dilarang:

- menyimpan role SSO mentah
- menambah enum tanpa desain ulang

3. Aturan VALIDASI (WAJIB DIKODEKAN)

Saat insert/update `user_upp`:

- `user_id` harus ada di `users`
- `upp_id` harus ada di `upps`
- `(user_id, upp_id, peran)` unik
- Satu user boleh punya banyak peran di UPP yang sama
- Peran yang sama tidak boleh dobel

4. Pola Pengisian (CONTOH NYATA)

Contoh 1 — Admin UPP

user_id        = 12  (Budi)
upp_id         = 5   (Bagian Organisasi)
peran          = admin_upp
aktif          = true
ditetapkan_oleh= 1   (Superadmin)

Contoh 2 — Verifikator lintas UPP

Siti memverifikasi 3 UPP

(user_id=20, upp_id=5, peran=verifikator)
(user_id=20, upp_id=7, peran=verifikator)
(user_id=20, upp_id=9, peran=verifikator)

5. Cara Sistem Menggunakan `user_upp`

Saat login:

- Ambil `users.id`
- Ambil semua `user_upp` aktif milik user
- Tentukan daftar UPP dan peran per UPP

Saat akses fitur:

- F01 → cek `admin_upp`
- F02 → cek `verifikator`
- Manajemen → cek `admin_organisasi` / `superadmin`

Tidak ada pengecekan role di tabel lain.

6. Hubungan dengan Tabel Legacy

| Tabel | Status |
|-------|--------|
| users | AKTIF |
| upp   | AKTIF |
| user_upp | AKTIF (utama) |
| user_unit_roles | LEGACY (read-only) |

Setelah stabil: `user_unit_roles` boleh dihapus atau disimpan untuk audit lama.

7. Checklist “AMAN PRODUKSI”

Sebelum lanjut F01–F03, pastikan:

- Tidak ada role di `users`
- Semua akses baca dari `user_upp`
- `upps` tidak punya kolom user
- Mutasi pejabat → update `user_upp`, bukan data penilaian

Kesimpulan keras tapi sehat:

SSO memberi identitas. UPP memberi konteks. `user_upp` memberi kekuasaan.

Jika salah satu dicampur, sistem akan rapuh.

---
Dokumen ini otomatis dibuat dari permintaan tugas; simpan sebagai pedoman resmi tim.
