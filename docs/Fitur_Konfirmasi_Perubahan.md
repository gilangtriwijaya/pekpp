# PRD — Fitur Konfirmasi Perubahan Indikator F01 & Diff Label F02

**Sistem:** PEKPPP  
**Modul:** F01 (Self-Assessment UPP) & F02 (Validasi)  
**Status Dokumen:** Ready for Implementation  
**Dibuat untuk:** AI Coding Agent

---

## 1. Latar Belakang & Tujuan

Saat ini, jika sebuah UPP sudah divalidasi di F02 namun statusnya diturunkan kembali ke draft (oleh admin/validator), UPP dapat mengisi ulang F01 dan submit kembali. Masalahnya: validator harus mengecek **semua indikator dari awal** meski UPP hanya mengubah sebagian kecil.

Fitur ini bertujuan:
1. Memberikan mekanisme konfirmasi di F01 agar UPP secara eksplisit menandai indikator mana yang mereka ubah.
2. Memanfaatkan flag tersebut di F02 untuk membantu validator fokus hanya pada indikator yang berubah — indikator yang tidak berubah mendapat skor otomatis dari validasi sebelumnya.

---

## 2. Pemahaman Alur Sistem yang Wajib Dipahami Agent

> **STOP. Sebelum menulis satu baris kode pun, agent WAJIB membaca dan memahami seluruh bagian ini.**

### 2.1 Alur Data Utama

```
UPP mengisi F01 → Submit → Status: "submitted"
       ↓
Validator membuka F02 → Validasi per indikator (beri skor) → Selesai
       ↓
[Opsional] Admin/Validator turunkan status UPP kembali ke "draft"
       ↓
UPP mengisi ulang F01 (autofill dari jawaban terakhir) → Submit baru
       ↓
F02 baru terbentuk mengacu ke submission F01 yang baru
```

### 2.2 Karakteristik Data yang Harus Dimengerti

- **F01 tidak membawa skor apapun.** F01 hanya berisi jawaban self-assessment (pilihan ganda, pilihan banyak, angka, narasi, dll). Skor sepenuhnya diberikan oleh validator di F02.
- **Setiap submit F01 menghasilkan record baru** dengan ID baru. Jawaban lama tidak ditimpa — disimpan semua. Ada flag/kolom penanda submission mana yang terbaru/aktif.
- **Setiap record F02 mengacu ke satu `f01_id` tertentu.** Ketika UPP submit ulang dan menghasilkan F01 baru, maka F02 yang baru juga merupakan record baru yang mengacu ke F01 baru tersebut. F02 lama tetap ada di database.
- **Bukti dukung berupa link eksternal** (Google Drive, umumnya folder). Link yang sama bisa isinya berubah tanpa URL berubah. Oleh karena itu **diff bukti dukung tidak dilakukan** — validator tetap wajib cek bukti dukung secara manual.
- **Jawaban F01 per indikator bisa berbeda tipe:** angka, pilihan tunggal, pilihan banyak, narasi teks. Agent harus inspect skema tabel untuk memahami bagaimana jawaban per tipe disimpan.

### 2.3 Langkah Wajib Sebelum Implementasi

Agent harus melakukan hal berikut **sebelum implementasi:**

1. **Baca dan pahami skema database** — identifikasi tabel untuk:
   - Submission F01 (tabel utama dan tabel jawaban per indikator/pertanyaan)
   - Record F02 / validasi (tabel utama dan tabel skor per indikator)
   - Tabel indikator, aspek, pertanyaan
   - Relasi UPP ke submission F01 (kolom apa yang menjadi foreign key ke entitas UPP)
   - Flag/kolom penanda submission terbaru di tabel F01

2. **Trace relasi antar tabel** untuk memahami bagaimana cara:
   - Menemukan submission F01 sebelumnya dari UPP yang sama
   - Menemukan record F02 validasi yang mengacu ke submission F01 sebelumnya tersebut
   - Mengambil skor per indikator dari F02 lama berdasarkan indikator yang sama

3. **Identifikasi komponen Blade dan Alpine.js** yang digunakan di:
   - Halaman F01 (tampilan per indikator, struktur form, cara data dikirim)
   - Halaman F02 (tampilan aspek di awal, tab indikator, konten per indikator, input skor)

4. **Identifikasi controller dan route** yang menangani:
   - Submit F01 per indikator (jika ada) dan submit utama F01
   - Simpan skor F02 per indikator

5. **Cek apakah sudah ada mekanisme autofill** jawaban lama saat UPP membuka F01 dalam status draft setelah diturunkan — jika sudah ada, pahami cara kerjanya sebelum memodifikasi.

---

## 3. Spesifikasi Fitur

### 3.1 Perubahan di Sisi Database

#### Tabel Baru atau Kolom Baru: Flag Konfirmasi Perubahan

Diperlukan cara menyimpan flag "indikator ini dikonfirmasi berubah oleh UPP" yang terikat ke submission F01 tertentu dan indikator tertentu.

**Pendekatan yang disarankan:**

Tambahkan kolom `is_changed` (boolean, default `false`) pada tabel yang menyimpan jawaban per indikator di F01. Jika tabel jawaban F01 sudah terpecah per indikator/pertanyaan, kolom ini cukup ditambahkan di level indikator (bukan per pertanyaan).

> Agent harus menentukan sendiri pendekatan yang paling sesuai dengan skema yang ada. Jangan membuat tabel baru jika kolom tambahan di tabel yang sudah ada sudah cukup.

**Aturan nilai flag:**
- Default `false` saat submission baru dibuat (autofill dari jawaban lama)
- Berubah menjadi `true` saat UPP mengkonfirmasi ingin mengubah indikator tersebut
- Flag ini tidak bisa dikembalikan ke `false` setelah dikonfirmasi (dalam satu sesi pengisian)
- Flag hanya berlaku untuk submission F01 yang berstatus draft (belum disubmit)

---

### 3.2 Fitur di F01 — Konfirmasi Perubahan per Indikator

#### Konteks UI F01 Saat Ini
- Ada navigasi aspek di awal
- Klik aspek → muncul tab-tab indikator horizontal
- Satu halaman menampilkan satu indikator beserta pertanyaan-pertanyaannya
- Saat status draft setelah diturunkan, jawaban lama sudah autofill

#### Behavior yang Diinginkan

**Default state setiap indikator:**
- Semua field jawaban dalam kondisi **read-only / disabled**
- Tampilan visual jelas bahwa ini mode baca, bukan mode edit (gunakan styling yang sudah ada di project, misalnya input dengan background muted atau pointer-events-none)

**Tombol "Ubah Jawaban Indikator Ini":**
- Letakkan tombol ini secara eksplisit dan mudah terlihat di area atas atau bawah konten indikator — jangan tersembunyi atau terlalu kecil
- Gunakan warna/styling secondary button yang sudah ada di project (jangan buat styling baru yang tidak konsisten)
- Label tombol harus jelas: contoh `✏️ Ubah Jawaban Indikator Ini` atau sesuaikan dengan bahasa yang konsisten di aplikasi

**Alur setelah tombol diklik:**
1. Muncul **modal/popup konfirmasi** dengan pesan yang jelas, contoh:
   > *"Anda akan mengubah jawaban pada indikator ini. Setelah dikonfirmasi, indikator ini akan ditandai sebagai 'berubah' dan validator akan diarahkan untuk memeriksa ulang bagian ini. Lanjutkan?"*
2. Tombol di modal: `Ya, Ubah` dan `Batal`
3. Jika `Ya, Ubah` diklik:
   - Modal ditutup
   - Field jawaban pada indikator tersebut berubah menjadi **aktif/editable**
   - Tombol "Ubah Jawaban" diganti dengan visual penanda bahwa indikator ini sudah dalam mode edit (contoh: badge `✏️ Dalam Mode Edit` atau border/background oranye/kuning pada card indikator)
   - Flag `is_changed` di backend di-set `true` untuk indikator ini (bisa via AJAX/request terpisah atau disertakan saat save)
4. Jika `Batal` diklik: tidak ada perubahan, indikator tetap read-only

**Tombol Save per Indikator:**
- Setiap indikator memiliki tombol **Save sendiri** yang menyimpan jawaban indikator tersebut langsung ke database (bukan menunggu submit utama)
- Tombol Save hanya aktif/visible setelah indikator masuk mode edit
- Saat Save berhasil: tampilkan feedback sukses (toast/notifikasi kecil yang sudah digunakan di project)
- Tujuan: UPP bisa menyimpan progress meski tidak menyelesaikan semua indikator dalam satu sesi

**Tombol Submit Utama:**
- Hanya bisa diklik **sekali** dan mengubah status submission ke "submitted"
- Setelah diklik, UPP tidak bisa mengedit lagi
- Logika validasi sebelum submit: pastikan semua indikator yang masuk mode edit sudah di-save (cek di frontend sebelum submit, tampilkan peringatan jika ada yang belum di-save)

#### Yang TIDAK Boleh Dilakukan di F01
- Jangan ubah tampilan atau alur indikator yang tidak masuk mode edit — biarkan persis seperti sebelumnya (read-only, autofill)
- Jangan paksa UPP untuk mengubah semua indikator — fitur ini opsional per indikator
- Jangan hilangkan data jawaban lama saat mode edit diaktifkan — data lama tetap sebagai nilai awal field yang bisa diubah
- Jangan buat request ke server hanya karena tombol "Ubah" diklik tanpa konfirmasi modal terlebih dahulu

---

### 3.3 Fitur di F02 — Diff Label untuk Validator

#### Konteks UI F02 Saat Ini
- Halaman awal menampilkan daftar aspek
- Klik aspek → masuk ke tampilan dengan tab-tab indikator horizontal
- Klik tab indikator → konten indikator tampil (berisi tampilan jawaban F01 read-mode + input skor + kolom komentar validator)

#### Sumber Data untuk Diff Label
Flag `is_changed` yang disimpan di submission F01 terbaru (yang sedang divalidasi di F02 ini) adalah sumber utama untuk menentukan label di F02.

#### Behavior yang Diinginkan

**A. Di Halaman Daftar Aspek (Halaman Awal F02):**

Setiap card/item aspek ditambahkan:
- **Ringkasan indikator:** contoh tampilan `3 indikator berubah · 9 indikator tidak berubah`
- **Efek visual pada aspek yang memiliki indikator berubah:** gunakan warna aksen atau border yang sudah ada di design system project (misalnya border kiri berwarna atau badge count di pojok card)
- Aspek yang **semua indikatornya tidak berubah** tetap bisa diakses tapi bisa diberi styling muted/lebih redup

**B. Di Tab-Tab Indikator:**

- Tab indikator yang **berubah (is_changed = true):**
  - Tampilkan dot/badge kecil berwarna (merah/oranye) di tab
  - Teks tab dalam warna normal
  - Contoh: `Indikator 3 🔴`

- Tab indikator yang **tidak berubah (is_changed = false):**
  - Tampilkan ikon centang atau warna muted pada tab
  - Contoh: `Indikator 1 ✓` dengan teks abu-abu
  - Tidak perlu diblokir — tetap bisa diklik dan diakses

**C. Di Konten Indikator (Saat Tab Diklik):**

Tambahkan **banner/alert** di bagian paling atas konten indikator, sebelum tampilan pertanyaan F01:

- Jika `is_changed = true`:
  ```
  ⚠️  UPP melaporkan perubahan pada indikator ini.
      Silakan periksa jawaban dan bukti dukung sebelum memberikan skor.
  ```
  Gunakan warna kuning/oranye (warning). Input skor dalam kondisi kosong, wajib diisi manual.

- Jika `is_changed = false`:
  ```
  ✅  Indikator ini tidak dilaporkan berubah oleh UPP.
      Skor dari validasi sebelumnya telah disalin secara otomatis.
      Anda tetap dapat mengubah skor jika diperlukan.
  ```
  Gunakan warna hijau (success/info). Input skor sudah terisi dengan nilai dari validasi sebelumnya.

**D. Carry-Over Skor Otomatis untuk Indikator Tidak Berubah:**

Ini adalah bagian paling kritis secara teknis. Agent harus hati-hati dalam implementasi ini.

**Logika lookup skor lama:**
1. Dari record F02 yang sedang dibuka, dapatkan `f01_id` yang dirujuk
2. Dari `f01_id` tersebut, dapatkan identitas UPP (misal `upp_id` atau entitas yang merepresentasikan UPP)
3. Cari submission F01 **sebelumnya** dari UPP yang sama (submission sebelum yang sekarang — bukan yang terbaru/aktif saat ini)
4. Dari submission F01 sebelumnya itu, cari record F02 validasi yang mengacunya
5. Dari record F02 lama tersebut, ambil skor per indikator
6. Salin skor tersebut sebagai nilai awal input skor di F02 yang sedang dibuka — **hanya untuk indikator yang `is_changed = false`**

**Aturan carry-over:**
- Jika tidak ditemukan F02 validasi sebelumnya (ini adalah submission pertama UPP): tidak ada carry-over, semua input skor kosong
- Jika F02 sebelumnya ada tapi skor indikator tertentu belum pernah diisi: input skor tetap kosong untuk indikator tersebut
- Carry-over dilakukan saat F02 pertama kali dibuka/diload — bukan setiap kali tab indikator diklik
- Skor yang di-carry over **tetap bisa diubah oleh validator** — ini hanya nilai awal, bukan nilai yang dikunci
- Carry-over hanya terjadi sekali saat F02 baru dibuat/diinisiasi — jika validator sudah mulai mengisi, jangan overwrite nilai yang sudah diubah validator

**Cara implementasi carry-over yang disarankan:**
Lakukan carry-over di sisi backend saat record F02 baru pertama kali diinisiasi (bukan di frontend). Simpan skor carry-over ke tabel skor F02 yang baru dengan flag tambahan `is_carried_over = true` (boolean). Flag ini berguna untuk audit dan untuk membedakan "skor yang benar-benar divalidasi validator" vs "skor yang hanya disalin otomatis".

---

## 4. Rules & Batasan Implementasi

### 4.1 Wajib Diikuti

- **Gunakan komponen Alpine.js yang sudah ada** untuk modal konfirmasi, toggle state, dan interaksi UI. Jangan import library baru kecuali memang tidak ada cara lain.
- **Gunakan class Tailwind yang sudah digunakan di project** — jangan buat custom CSS baru. Cek file Blade yang ada untuk memahami design system yang dipakai.
- **Semua perubahan database harus menggunakan Laravel Migration** — jangan alter tabel secara manual.
- **Carry-over skor harus dilakukan di backend (Laravel)** — jangan lakukan logika bisnis ini di frontend/JavaScript.
- **Setiap endpoint baru harus dilindungi middleware yang sudah ada** — cek middleware apa yang digunakan di route F01 dan F02 yang existing, gunakan yang sama.
- **Gunakan transaction database** untuk operasi carry-over skor — jika sebagian gagal, semua harus rollback.
- **Ikuti konvensi penamaan yang sudah ada** di controller, model, migration, dan view — jangan buat konvensi baru.

### 4.2 Yang Harus Dihindari

- **Jangan overwrite atau hapus data lama** — history jawaban F01 dan skor F02 lama harus tetap utuh.
- **Jangan lakukan diff otomatis berbasis konten jawaban** — satu-satunya sumber kebenaran "berubah atau tidak" adalah flag `is_changed` yang dikonfirmasi manual oleh UPP.
- **Jangan blokir validator** dari mengakses indikator yang `is_changed = false` — label hanya bersifat informatif, bukan pembatas akses.
- **Jangan buat asumsi tentang nama tabel, kolom, atau relasi** — selalu inspect skema aktual terlebih dahulu.
- **Jangan ubah alur submit utama F01 yang sudah berjalan** — hanya tambahkan mekanisme flag dan save per indikator di atasnya.
- **Jangan tampilkan fitur konfirmasi (tombol Ubah) jika submission F01 bukan dalam status draft** — fitur ini hanya aktif saat UPP sedang mengisi ulang setelah status diturunkan.
- **Jangan salin skor carry-over jika F02 sudah pernah diproses** (validator sudah mulai mengisi) — cek apakah record skor di F02 sudah ada sebelum melakukan carry-over.

---

## 5. Checklist Verifikasi Sebelum Selesai

Agent harus memastikan semua poin berikut terpenuhi sebelum menyatakan implementasi selesai:

**Database:**
- [ ] Migration berjalan tanpa error
- [ ] Kolom `is_changed` (atau setara) tersedia dan default `false`
- [ ] Kolom `is_carried_over` di tabel skor F02 tersedia dan default `false`

**F01 — UPP:**
- [ ] Semua indikator read-only secara default saat status draft setelah diturunkan
- [ ] Tombol "Ubah Jawaban Indikator Ini" terlihat jelas di tiap indikator
- [ ] Modal konfirmasi muncul saat tombol diklik
- [ ] Setelah konfirmasi, indikator unlock dan ada visual penanda mode edit
- [ ] Tombol Save per indikator berfungsi dan menyimpan ke DB termasuk flag `is_changed = true`
- [ ] Submit utama hanya bisa dilakukan sekali
- [ ] Indikator yang tidak dikonfirmasi edit tetap read-only dan tidak ikut tersimpan ulang

**F02 — Validator:**
- [ ] Halaman daftar aspek menampilkan ringkasan indikator berubah/tidak per aspek
- [ ] Aspek dengan indikator berubah mendapat efek visual berbeda
- [ ] Tab indikator yang berubah mendapat penanda visual (dot/badge)
- [ ] Tab indikator yang tidak berubah mendapat penanda visual berbeda (muted/ikon)
- [ ] Banner status muncul di atas konten tiap indikator
- [ ] Skor carry-over terisi otomatis untuk indikator `is_changed = false`
- [ ] Skor carry-over **tidak** terisi jika tidak ada F02 validasi sebelumnya
- [ ] Validator tetap bisa mengubah skor carry-over
- [ ] Flag `is_carried_over` tersimpan dengan benar di DB

**Umum:**
- [ ] Tidak ada data lama yang terhapus atau tertimpa
- [ ] Semua route dilindungi middleware yang sesuai
- [ ] Tidak ada library baru yang diimport tanpa alasan kuat
- [ ] Konvensi kode konsisten dengan yang sudah ada di project

---

## 6. Urutan Implementasi yang Disarankan

Implementasi sebaiknya dilakukan berurutan untuk menghindari dependency yang belum tersedia:

1. **Inspect & dokumentasi skema** — pahami tabel, kolom, relasi yang relevan
2. **Buat migration** — tambah kolom `is_changed` dan `is_carried_over`
3. **Backend F01** — endpoint save per indikator + set flag `is_changed`
4. **UI F01** — tombol ubah, modal konfirmasi, mode edit, tombol save per indikator
5. **Backend F02** — logika lookup skor lama + carry-over saat F02 diinisiasi
6. **UI F02 — Konten indikator** — banner status + input skor dengan nilai carry-over
7. **UI F02 — Tab indikator** — penanda visual berubah/tidak berubah
8. **UI F02 — Halaman aspek** — ringkasan dan efek visual per aspek
9. **Testing end-to-end** — simulasi full flow: submit F01 → validasi F02 → turun status → submit ulang F01 → validasi F02 baru

---

*Dokumen ini adalah panduan rancangan. Agent bertanggung jawab untuk menyesuaikan detail implementasi dengan kondisi aktual codebase yang ditemukan saat inspeksi.*