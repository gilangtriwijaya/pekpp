Berikut prompt roadmap lengkapnya:

---

## PROMPT UNTUK AGENT

```
# Fitur: Salin Instrumen Antar Periode — PEKPP

## Konteks Proyek
- Aplikasi: Laravel, lokasi di `/home/deploy/apps/pekpp`
- UI: Blade + Tailwind CSS + Alpine.js (bukan Filament)
- Database: MySQL, nama DB `pekpp`
- Fitur ini ditambahkan ke halaman manajemen Periode yang sudah ada

## Yang Sudah Dikerjakan (Jangan Diubah)
- Tabel `aspek` sudah punya kolom: `id`, `periode_id`, `kode`, `nama`, 
  `domain`, `urutan`, `bobot`, `aktif`, `keterangan`, `deleted_at`
- Tabel `indikator` sudah punya kolom: `id`, `aspek_id`, `kode`, `nama`, 
  `deskripsi`, `bukti_dukung`, `urutan`, `aktif`, `deleted_at`
- Tabel `pertanyaan` sudah punya kolom: `id`, `indikator_id`, 
  `parent_pertanyaan_id`, `show_when`, `skip_if_answer`, `kode`, `label`, 
  `tipe_input`, `opsi_jawaban`, `allow_lainnya`, `wajib`, `urutan`, 
  `aktif`, `min`, `max`, `deleted_at`
- Kode aspek berformat: `A1`, `A2`, `A3`, dst (tanpa tahun)
- Kode indikator berformat: `A1_I1`, `A1_I2`, dst (sudah diupdate, 
  tidak pakai tahun)
- Kode pertanyaan masih berformat lama dengan tahun (biarkan, tidak perlu diubah)
- Relasi: `aspek` belongs to `periode`, `indikator` belongs to `aspek`, 
  `pertanyaan` belongs to `indikator`
- Pertanyaan bisa punya parent (`parent_pertanyaan_id`) — pertanyaan conditional

## Yang Harus Dicek Agent Sebelum Mulai
Sebelum menulis satu baris kode pun, agent WAJIB membaca dan memahami:

1. File route: cek di `routes/web.php` — route apa yang sudah ada untuk 
   periode, pola URL-nya, middleware apa yang dipakai, nama route-nya
2. Controller periode: cari dan baca controller yang menangani CRUD periode — 
   pahami method yang sudah ada, namespace, cara validasi, cara return response
3. Model periode, aspek, indikator, pertanyaan: baca semua relasi yang 
   sudah didefinisikan
4. Blade halaman list periode: cari dan baca view yang menampilkan tabel 
   periode — pahami struktur HTML, bagaimana tombol aksi lain (edit, aktifkan) 
   diimplementasikan, apakah sudah ada Alpine.js di halaman ini
5. Cek apakah sudah ada modal component yang reusable di project ini, 
   atau setiap modal dibuat inline
6. Cek cara project ini handle AJAX/fetch — apakah ada pattern tertentu 
   untuk request async, CSRF token, response format JSON

Tujuan pengecekan ini: MENYESUAIKAN implementasi baru dengan pola yang 
sudah ada, bukan memaksakan pola baru.

## Fitur yang Harus Dibangun

### 1. Tombol Trigger di Halaman List Periode
- Tambahkan tombol "Salin Instrumen" di baris aksi setiap periode
- Posisi: sejajar dengan tombol aksi yang sudah ada (edit, aktifkan, dll)
- Periode yang ada di baris = periode TUJUAN (yang akan menerima salinan)
- Klik tombol → buka modal salin instrumen, kirim `periode_id` tujuan ke modal

### 2. Modal Salin Instrumen
Layout modal:

```
┌─────────────────────────────────────────────────┐
│  Salin Instrumen ke: [Nama Periode Tujuan]  [X]  │
├─────────────────────────────────────────────────┤
│  Salin dari Periode                              │
│  [dropdown pilih periode sumber ▼]              │
│  (exclude periode tujuan dari pilihan)           │
│                                                  │
│  ┌───────────────────────────────────────────┐  │
│  │ AREA INI SCROLLABLE                       │  │
│  │                                           │  │
│  │ ☑ A1 · Nama Aspek                        │  │
│  │   ├─ ☑ A1_I1 · Nama Indikator           │  │
│  │   │    ├─ ☑ Pertanyaan 1                │  │
│  │   │    └─ ☑ Pertanyaan 2                │  │
│  │   └─ ☑ A1_I2 · Nama Indikator           │  │
│  │                                           │  │
│  │ ☑ A2 · Nama Aspek                        │  │
│  │   └─ ...                                 │  │
│  └───────────────────────────────────────────┘  │
│                                                  │
│  Jika kode sudah ada di periode tujuan:          │  ← FIXED, tidak ikut scroll
│  ○ Skip (pertahankan yang ada)                   │
│  ○ Overwrite (timpa dengan data sumber)          │
│                                                  │
│            [Batal]  [Salin yang Dipilih]         │
└─────────────────────────────────────────────────┘
```

Detail perilaku modal:
- Dropdown periode sumber: saat dipilih, load tree instrumen via AJAX
- Tree checkbox menggunakan tri-state: checked / indeterminate / unchecked
- Centang aspek → otomatis centang semua indikator dan pertanyaan di dalamnya
- Uncentang aspek → otomatis uncentang semua di dalamnya
- Centang/uncentang sebagian indikator dalam aspek → aspek jadi indeterminate
- Centang indikator → centang semua pertanyaan di dalamnya
- Centang/uncentang sebagian pertanyaan → indikator jadi indeterminate
- Pertanyaan conditional (punya parent_pertanyaan_id) ditampilkan 
  menjorok di bawah pertanyaan induknya, dengan label "(kondisional)"
- Jika pertanyaan conditional dicentang, pertanyaan induknya 
  HARUS ikut tercentang otomatis
- Area tree: scrollable dengan max-height yang wajar (misal max-h-96)
- Bagian radio "jika kode sudah ada": FIXED di luar area scroll, 
  selalu terlihat
- Default radio: Skip

### 3. Endpoint Load Tree (AJAX)
- Method: GET
- URL: `/periode/{periode_id}/instrumen-tree?sumber={sumber_periode_id}`
- Response JSON: nested struktur aspek > indikator > pertanyaan
- Struktur response:
```json
[
  {
    "id": 1,
    "kode": "A1",
    "nama": "Nama Aspek",
    "indikator": [
      {
        "id": 2,
        "kode": "A1_I1", 
        "nama": "Nama Indikator",
        "pertanyaan": [
          {
            "id": 5,
            "kode": "2026_P1",
            "label": "Teks pertanyaan",
            "parent_pertanyaan_id": null
          },
          {
            "id": 6,
            "kode": "2026_P2",
            "label": "Teks pertanyaan kondisional",
            "parent_pertanyaan_id": 5
          }
        ]
      }
    ]
  }
]
```

### 4. Endpoint Eksekusi Salin (AJAX)
- Method: POST
- URL: `/periode/{periode_id}/salin-instrumen`
- Request body JSON:
```json
{
  "sumber_periode_id": 1,
  "mode": "skip",  // atau "overwrite"
  "aspek_ids": [1, 2],
  "indikator_ids": [1, 2, 3, 4],
  "pertanyaan_ids": [1, 2, 3, 4, 5]
}
```
- Logic salin di backend:

  **Untuk setiap aspek yang dipilih:**
  - Cek apakah aspek dengan `kode` yang sama sudah ada di periode tujuan
  - Jika belum ada: INSERT aspek baru dengan `periode_id` = tujuan, 
    salin semua kolom kecuali `id`, `periode_id`, `created_at`, `updated_at`
  - Jika sudah ada + mode skip: gunakan aspek yang ada, jangan ubah
  - Jika sudah ada + mode overwrite: UPDATE aspek yang ada dengan data sumber

  **Untuk setiap indikator yang dipilih:**
  - Resolve `aspek_id` baru: cari aspek di periode tujuan yang kodenya 
    sama dengan aspek induk indikator sumber
  - Cek apakah indikator dengan `kode` yang sama sudah ada di aspek tujuan
  - Jika belum ada: INSERT dengan `aspek_id` yang sudah di-resolve
  - Jika sudah ada + skip: lewati
  - Jika sudah ada + overwrite: UPDATE

  **Untuk setiap pertanyaan yang dipilih:**
  - Resolve `indikator_id` baru: cari indikator di periode tujuan yang 
    kodenya sama dengan indikator induk pertanyaan sumber
  - Untuk pertanyaan non-conditional (parent_pertanyaan_id null): 
    INSERT/UPDATE berdasarkan `kode`
  - Untuk pertanyaan conditional: 
    - Resolve `parent_pertanyaan_id` baru: cari pertanyaan induk 
      yang sudah disalin ke periode tujuan by kode
    - Jika pertanyaan induk tidak ditemukan di tujuan: SKIP pertanyaan 
      conditional ini dan catat di response warning
  - Salin semua kolom pertanyaan kecuali `id`, `indikator_id`, 
    `parent_pertanyaan_id`, `created_at`, `updated_at`

- Response JSON:
```json
{
  "success": true,
  "summary": {
    "aspek_disalin": 3,
    "aspek_dilewati": 0,
    "indikator_disalin": 10,
    "indikator_dilewati": 2,
    "pertanyaan_disalin": 25,
    "pertanyaan_dilewati": 1,
    "warning": ["Pertanyaan kondisional P-X dilewati karena induk tidak ditemukan"]
  }
}
```
- Seluruh operasi dibungkus dalam satu DB transaction — jika ada error, 
  semua rollback

### 5. Tampilan Hasil Setelah Salin
- Setelah response sukses, tampilkan summary di dalam modal 
  (ganti konten modal, jangan tutup dulu)
- Tampilkan jumlah yang disalin dan dilewati per level
- Jika ada warning, tampilkan list warning
- Tombol "Tutup" di bawah summary

## Batasan dan Larangan
- JANGAN ubah struktur tabel yang sudah ada
- JANGAN ubah controller atau route yang sudah ada, hanya tambahkan
- JANGAN install package baru tanpa konfirmasi
- JANGAN gunakan Livewire atau Filament — murni Blade + Alpine.js + fetch API
- JANGAN hardcode credential atau konfigurasi
- Seluruh teks UI dalam Bahasa Indonesia
- Gunakan pola kode yang konsisten dengan yang sudah ada di project 
  (sesuai hasil pengecekan di awal)

## Urutan Pengerjaan yang Disarankan
1. Baca semua file eksisting yang relevan (wajib sebelum mulai)
2. Tambah route (GET tree + POST salin)
3. Tambah method di controller periode
4. Tambah method di controller (atau service class jika project sudah 
   pakai service layer) untuk logika salin
5. Tambah blade modal component
6. Tambah Alpine.js logic untuk tree checkbox dan fetch
7. Tambah tombol trigger di halaman list periode
8. Test manual: salin dengan mode skip, salin dengan mode overwrite, 
   salin dengan pertanyaan conditional
```

---
