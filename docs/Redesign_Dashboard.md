# PRD: Refactor Dashboard — Aplikasi LAYANI Mandiri (PEKPPP)

**Versi:** 2.0  
**Tanggal:** Juni 2026  
**Status:** Ready for Development  

---

## 1. Latar Belakang

Dashboard saat ini adalah file blade monolitik 2000+ baris yang hanya melayani Admin Internal. Tujuan refactor ini:

- Memisahkan filosofi dashboard per role: **task-oriented** untuk Admin UPP, **monitoring-oriented** untuk Admin Internal
- Memecah satu file blade besar menjadi partial-partial yang spesifik dan maintainable
- Menambahkan dashboard khusus Admin UPP yang belum pernah ada sebelumnya
- Menyertakan fitur Pengumuman (migration, model, CRUD, widget)

---

## 2. Konteks Sistem

### 2.1 Integrasi SSO

Aplikasi LAYANI Mandiri menggunakan SSO sebagai auth. Role dari SSO dikonversi sebagai berikut:

| Role SSO | Role di PEKPPP | Keterangan |
|---|---|---|
| Superadmin | Admin Internal | ✅ Aktif |
| Admin Bagian Organisasi | Admin Internal | ✅ Aktif |
| Verifikator Global | Admin Internal | ✅ Aktif |
| Admin OPD | Admin UPP | ✅ Aktif |
| Admin Unit | Admin UPP | ✅ Aktif |
| Verifikator OPD | — | ⏭ Skip, belum digunakan |
| Verifikator Unit | — | ⏭ Skip, belum digunakan |

Deteksi role di eksisting menggunakan:
```php
// Eksisting (DashboardController):
$isGlobalUser = $user->hasGlobalRole([
    'superadmin', 'admin_organisasi', 'admin_bagian_organisasi', 'org_admin', 'org-admin'
]);

// Konversi ke PRD:
$isAdminUPP = !$isGlobalUser;
```

### 2.2 Struktur UPP

- UPP bersifat **flat**, tidak ada hierarki OPD → Unit di dalam PEKPPP
- 1 user UPP mengelola 1 UPP
- "UPP terdaftar" = user sudah di-assign ke UPP via tabel `user_upp` dengan `aktif = 1`
- Tidak ada kolom `periode_id` di tabel UPP — pendaftaran ke periode bukan via relasi tabel, melainkan via penugasan user aktif
- Logika cek UPP terdaftar di controller:
  ```php
  $uppTerdaftar = UserUpp::where('user_id', $user->id)->where('aktif', 1)->exists();
  ```

### 2.3 Sistem Penilaian F01 / F02 / F03

Penilaian terdiri dari 3 form dengan tujuan akhir mendapat nilai **IPP**:

| Form | Deskripsi | Skor |
|---|---|---|
| **F01** | Self assessment — UPP menjawab pertanyaan dan melampirkan bukti dukung | ❌ Tidak ada skor |
| **F02** | Validasi — validator menilai isian F01, memberi skor dan catatan per indikator/aspek | ✅ Ada skor per aspek |
| **F03** | Kuesioner online — survei kepuasan, tidak ada validasi, nilai = rata-rata | ✅ Ada skor |

**Rumus IPP:** `IPP = 75% × F02 + 25% × F03`

> ⚠️ Model `Aspek` (tabel `aspek`) = instrumen F01/F02. Model `F03Aspek` (tabel berbeda) = instrumen F03. Radar chart UPP berbasis `Aspek` F01/F02, bukan F03Aspek.

### 2.4 Periode Penilaian

- Hanya ada **1 periode aktif** pada satu waktu
- Kolom tanggal di tabel `periode`: **`tanggal_mulai`** dan **`tanggal_selesai`** (bukan `tanggal_awal`/`tanggal_akhir`)
- Status periode (open/kunci/arsip) diset manual — otomasi berdasarkan tanggal adalah backlog terpisah

### 2.5 Instrumen

- Hierarki: **Aspek → Indikator → Pertanyaan**
- Aspek dan Indikator relatif stabil antar periode
- Pertanyaan bisa berubah tiap periode
- Progress dan radar chart berbasis Aspek/Indikator — aman untuk perbandingan antar periode

### 2.6 Flow Status Pengisian F01 UPP

```
Belum Mulai      ← tidak ada record F01 sama sekali
    ↓ (simpan minimal 1 indikator)
Sedang Mengisi   ← status DB: 'draft' ATAU 'rolled_back'
    ↓ (submit final)
Menunggu Validasi ← status DB: 'submitted'
    ↓ (divalidasi Admin Internal)
Selesai          ← status DB: 'selesai'
```

**Mapping status DB → label PRD:**

| Status DB | Label Tampilan | Keterangan |
|---|---|---|
| *(tidak ada record)* | Belum Mulai | UPP belum pernah simpan apapun |
| `draft` | Sedang Mengisi | Pengisian berjalan |
| `rolled_back` | Sedang Mengisi | Dikembalikan validator, harus submit ulang |
| `submitted` | Menunggu Validasi | Sudah submit, menunggu validator |
| `selesai` | Selesai | Validasi selesai |

> Cek apakah di model `F01Pengisian` ada kolom `is_latest_version` — jika ada, selalu filter dengan kondisi ini untuk menghindari duplikasi data dari versi submit ulang.

---

## 3. Instruksi untuk Agent

> ⚠️ **Sebelum menulis kode apapun, agent wajib:**
> 1. Baca struktur model eksisting yang relevan: `Periode`, `Upp`, `UserUpp`, `User`, `Aspek`, `Indikator`, `F01Pengisian`, `F01AspekPengisian`, `F01IndikatorNilai`
> 2. Cek apakah query/scope yang dibutuhkan sudah ada di model atau repository
> 3. Cek apakah partial yang akan dibuat sudah pernah ada
> 4. Eksisting ada tapi kurang → **adapt**. Belum ada sama sekali → **buat baru**
> 5. Jangan hapus atau ubah fungsionalitas yang tidak termasuk scope dashboard ini

---

## 4. Struktur File Target

### 4.1 Dashboard (Views)

```
resources/views/dashboard/
├── index.blade.php                           ← tulis ulang jadi entry point ringkas
└── partials/
    ├── _header_greeting.blade.php            ← shared
    ├── _pengumuman_widget.blade.php          ← shared
    ├── _periode_banner.blade.php             ← shared
    ├── _progress_upp.blade.php               ← Admin UPP only
    ├── _hasil_penilaian_upp.blade.php        ← Admin UPP only (kondisional)
    ├── _radar_chart_upp.blade.php            ← Admin UPP only
    ├── _history_card.blade.php               ← Admin UPP only
    ├── _summary_cards_internal.blade.php     ← Admin Internal only
    ├── _progress_chart_internal.blade.php    ← Admin Internal only
    └── _deadline_alert.blade.php             ← Admin Internal only
```

### 4.2 Pengumuman (Fitur Baru)

```
app/Models/Pengumuman.php
app/Http/Controllers/PengumumanController.php
resources/views/pengumuman/
├── index.blade.php      ← list + CRUD (Admin Internal only)
├── create.blade.php
└── edit.blade.php
database/migrations/xxxx_create_pengumuman_table.php
database/seeders/PengumumanSeeder.php
```

> Pengumuman diakses via menu sidebar tersendiri, **bukan** di dalam folder dashboard.

---

## 5. `index.blade.php` — Entry Point

File ini hanya bertugas sebagai router. Tidak boleh ada logic atau query.

```blade
@extends('layouts.app')

@section('content')
    @include('dashboard.partials._header_greeting')
    @include('dashboard.partials._pengumuman_widget')
    @include('dashboard.partials._periode_banner')

    @if($isAdminUPP)
        @include('dashboard.partials._progress_upp')
        @include('dashboard.partials._hasil_penilaian_upp')
        @include('dashboard.partials._radar_chart_upp')
        @include('dashboard.partials._history_card')
    @else
        @include('dashboard.partials._summary_cards_internal')
        @include('dashboard.partials._progress_chart_internal')
        @include('dashboard.partials._deadline_alert')
    @endif
@endsection
```

---

## 6. Controller — `DashboardController@index`

Semua query dan kalkulasi di controller. Partial hanya konsumsi variabel.

```php
public function index()
{
    $user = auth()->user();
    $isAdminUPP = !$user->hasGlobalRole([
        'superadmin', 'admin_organisasi', 'admin_bagian_organisasi', 'org_admin', 'org-admin'
    ]);

    $data = [
        'isAdminUPP'   => $isAdminUPP,
        'pengumuman'   => $this->getPengumumanAktif(),
        'periodeAktif' => $this->getPeriodeAktif(),
    ];

    if ($isAdminUPP) {
        $data += $this->getDataUPP($user);
    } else {
        $data += $this->getDataInternal();
    }

    return view('dashboard.index', $data);
}
```

### Method yang dibutuhkan:

| Method | Return | Keterangan |
|---|---|---|
| `getPengumumanAktif()` | `Pengumuman\|null` | Pengumuman dengan `aktif = true`, terbaru |
| `getPeriodeAktif()` | `Periode\|null` | Periode dengan `is_aktif = true` |
| `getDataUPP($user)` | `array` | Semua variabel kebutuhan dashboard UPP |
| `getDataInternal()` | `array` | Semua variabel kebutuhan dashboard Internal |

---

## 7. Spesifikasi Partial — Admin UPP

### 7.1 `_header_greeting.blade.php`

**Variabel:** `$user`, `$uppName`

**Konten:**
- Salam berdasarkan waktu (Selamat Pagi/Siang/Sore/Malam)
- Nama lengkap user
- Nama UPP
- Tanggal hari ini

---

### 7.2 `_pengumuman_widget.blade.php`

**Variabel:** `$pengumuman` (nullable `Pengumuman`)

**Konten:**
- Tampil hanya jika `$pengumuman` tidak null
- Judul pengumuman
- Isi singkat (truncate jika panjang, dengan tombol expand/modal)
- Jika null → partial tidak render apapun

---

### 7.3 `_periode_banner.blade.php`

**Variabel:** `$periodeAktif` (nullable), `$uppTerdaftar` (bool)

| Kondisi | Tampilan |
|---|---|
| Periode ada, UPP terdaftar | Nama periode, `tanggal_mulai` – `tanggal_selesai`, status periode |
| Periode ada, UPP **tidak** terdaftar | Warning: "UPP Anda belum terdaftar pada periode ini. Hubungi Admin." |
| Periode null (fallback) | Info ringan: "Belum ada periode penilaian aktif saat ini." |

---

### 7.4 `_progress_upp.blade.php`

**Variabel:** `$progressPerAspek` (collection: `nama`, `terisi`, `total`), `$statusPengisian` (string), `$urlPengisian` (string)

**Konten:**
- Badge status keseluruhan
- Progress bar per aspek:
  ```
  Kebijakan        ████████░░  8/10 indikator
  Profesionalisme  ██████░░░░  6/10 indikator
  SDM              ░░░░░░░░░░  0/8  indikator
  ```
- Tombol CTA berdasarkan status:

| Status | CTA |
|---|---|
| `belum_mulai` | Tombol aktif "Mulai Pengisian" |
| `sedang_mengisi` | Tombol aktif "Lanjutkan Pengisian" |
| `menunggu_validasi` | Tombol disabled "Menunggu Validasi" |
| `selesai` | Label "Pengisian Selesai", tanpa tombol |

---

### 7.5 `_hasil_penilaian_upp.blade.php` *(kondisional)*

**Variabel:** `$hasilPenilaian` (nullable: `nilai_f02`, `nilai_f03`, `nilai_ipp`, `predikat`)

**Tampil hanya jika** nilai F02 dan/atau F03 sudah tersedia untuk UPP ini di periode aktif.

**Konten:**
- Card nilai F02, F03, dan IPP
- Predikat akhir
- Catatan: F01 tidak memiliki skor — partial ini berbasis data F02/F03

**Jika `$hasilPenilaian` null:** partial tidak render apapun

---

### 7.6 `_radar_chart_upp.blade.php`

**Variabel:** `$radarData` (array per periode: `label`, `nilai_per_aspek`), `$periodeList` (list periode untuk toggle)

**Catatan penting:** radar chart berbasis skor F02 per aspek (dari `F01AspekPengisian` atau tabel F02 yang menyimpan skor validator). Bukan F03.

**Konten:**
- Radar chart — sumbu = nama aspek, nilai = skor per aspek
- Toggle checkbox per periode untuk overlay comparison
- Periode aktif ditampilkan default jika sudah ada skor F02

| Kondisi | Tampilan |
|---|---|
| Ada histori ≥ 1 periode lalu | Radar multi-layer, toggle aktif |
| UPP baru, periode aktif sudah ada skor | Radar 1 layer, label "Belum ada data periode sebelumnya" |
| Belum ada skor F02 sama sekali | Sembunyikan radar. Pesan: "Selesaikan pengisian untuk melihat grafik perkembangan Anda" |

**Library:** Chart.js v4.4.0 — sudah tersedia via CDN di `layouts/app.blade.php`. Tidak perlu install.

---

### 7.7 `_history_card.blade.php`

**Variabel:** `$periodeSebelumnya` (nullable: `nama`, `nilai_ipp`, `predikat`), `$deltaNilai` (nullable: float positif/negatif)

**Konten card:**
- Nama periode terakhir yang memiliki nilai IPP
- Nilai IPP + predikat
- Indikator delta naik/turun vs periode sebelumnya (jika ada ≥ 2 periode)
- Tombol "Lihat Detail Isian"

**Jika tidak ada histori:** pesan "Belum ada riwayat penilaian sebelumnya"

**Modal Detail Isian:**
- Tab per Aspek
- Per tab: list Indikator + nilai + predikat
- Tidak ada chart di dalam modal

---

## 8. Spesifikasi Partial — Admin Internal

### 8.1 `_summary_cards_internal.blade.php`

**Variabel:** `$summaryCards` (array dengan key: `total`, `belum_mulai`, `sedang_mengisi`, `menunggu_validasi`, `selesai` — masing-masing berisi `count` dan `list`)

**Query referensi untuk controller:**
```php
$periodeId = $periodeAktif->id;

// Belum mulai: UPP aktif yang tidak punya record F01 di periode ini
$belumMulai = Upp::where('aktif', 1)
    ->whereDoesntHave('f01Pengisian', fn($q) => $q->where('periode_id', $periodeId))
    ->get();

// Sedang mengisi: status draft atau rolled_back
$sedangMengisi = F01Pengisian::where('periode_id', $periodeId)
    ->whereIn('status', ['draft', 'rolled_back'])
    ->where('is_latest_version', true) // cek dulu apakah kolom ini ada
    ->with('upp')
    ->get();

// Menunggu validasi
$menungguValidasi = F01Pengisian::where('periode_id', $periodeId)
    ->where('status', 'submitted')
    ->where('is_latest_version', true)
    ->with('upp')
    ->get();

// Selesai
$selesai = F01Pengisian::where('periode_id', $periodeId)
    ->where('status', 'selesai')
    ->where('is_latest_version', true)
    ->with('upp')
    ->get();
```

> ⚠️ Cek dulu apakah kolom `is_latest_version` ada di tabel `f01_pengisian`. Jika tidak ada, sesuaikan query.

**5 Card berurutan:**
```
Total UPP | Belum Mulai | Sedang Mengisi | Menunggu Validasi | Selesai
```

**Interaksi:**
- Klik card (kecuali Total UPP) → expand panel inline di bawah semua card
- Panel: tabel Nama UPP | Nama User | Status
- Hanya 1 panel terbuka sekaligus
- Klik card aktif yang sama → collapse
- Card "Total UPP" tidak punya panel

---

### 8.2 `_progress_chart_internal.blade.php`

**Variabel:** `$progressPerUPP` (collection: `nama_upp`, `status`, `persen_progress`)

**Konten:**
- Bar chart horizontal — 1 bar per UPP
- Warna bar per status: abu=belum mulai, kuning=sedang, biru=menunggu validasi, hijau=selesai
- Label nama UPP di sumbu Y
- Library: Chart.js (sudah tersedia)

---

### 8.3 `_deadline_alert.blade.php`

**Variabel:** `$uppDeadlineAlert` (collection), `$thresholdHari` (int, default: 7)

**Logika:**
```
sisa_hari = periodeAktif.tanggal_selesai - today()
Tampilkan UPP jika: sisa_hari <= threshold AND status != 'selesai'
Urutkan: sisa_hari ASC
```

**Konten:**
- Header: "X UPP belum menyelesaikan pengisian — sisa N hari"
- List: Nama UPP | Status | Sisa hari
- Jika tidak ada yang masuk threshold → partial tidak render apapun

---

## 9. Fitur Pengumuman

### 9.1 Migration

Buat tabel `pengumuman` dengan kolom minimal:

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint PK | |
| `judul` | string | |
| `isi` | text | |
| `aktif` | boolean | default false |
| `published_at` | timestamp nullable | |
| `expired_at` | timestamp nullable | Jika null = tidak ada kadaluarsa |
| `created_by` | FK users | |
| `timestamps` | | |

### 9.2 Model `Pengumuman`

- Scope `aktif()`: filter `aktif = true` dan `published_at <= now()` dan (`expired_at` null atau `expired_at >= now()`)
- Cast `aktif` ke boolean

### 9.3 Controller CRUD `PengumumanController`

- Method: `index`, `create`, `store`, `edit`, `update`, `destroy`
- Akses: **Admin Internal only** (middleware role)
- Route prefix: `/pengumuman`

### 9.4 Views Pengumuman

```
resources/views/pengumuman/
├── index.blade.php    ← tabel list + tombol tambah/edit/hapus/toggle aktif
├── create.blade.php   ← form tambah
└── edit.blade.php     ← form edit
```

### 9.5 Seeder

Buat `PengumumanSeeder` dengan 1–2 data dummy pengumuman aktif untuk development/testing.

### 9.6 Route & Menu

- Tambahkan route resource `/pengumuman` di `routes/web.php`
- Tambahkan menu "Pengumuman" di sidebar — tampil hanya untuk Admin Internal

---

## 10. Edge Cases yang Wajib Di-handle

| Kondisi | Handling |
|---|---|
| UPP tidak terdaftar (`user_upp.aktif = 0` atau tidak ada record) | `_periode_banner` tampilkan warning, partial lain tetap aman |
| UPP baru tanpa histori | `_history_card` tampilkan pesan kosong, `_radar_chart_upp` sembunyikan chart |
| Belum ada skor F02/F03 | `_hasil_penilaian_upp` dan `_radar_chart_upp` tidak render |
| `$pengumuman` null | `_pengumuman_widget` tidak render |
| Tidak ada UPP masuk threshold deadline | `_deadline_alert` tidak render |
| `$periodeAktif` null | Tampilkan fallback sederhana, jangan error |
| Tabel `pengumuman` belum ada (saat migrasi belum dijalankan) | Wrap query pengumuman di try-catch atau cek schema dulu |

---

## 11. Catatan Teknis

- **Semua query di controller**, tidak ada query di blade partial
- **Eager loading** wajib untuk menghindari N+1
- **Nama kolom tanggal periode:** `tanggal_mulai` dan `tanggal_selesai` (bukan `tanggal_awal`/`tanggal_akhir`)
- **Chart.js** v4.4.0 sudah tersedia via CDN di layout utama — tidak perlu install ulang
- **Styling:** project menggunakan Tailwind CSS v4 — ikuti konvensi yang sudah ada. Hindari menambah custom CSS inline di blade baru jika bisa digantikan Tailwind
- **Dashboard Internal lama** (card IPP/F02/F03 score) digantikan sepenuhnya oleh konsep baru (card status pengisian). Data skor tetap tersedia di menu Analitik

---

## 12. Out of Scope

- Otomasi status periode (open → kunci → arsip) berdasarkan tanggal
- Menu Analitik (sudah ada, tidak diubah)
- Role Verifikator (belum digunakan)
- Fitur validasi F02 (tidak diubah)

---

## 13. Definition of Done

- [ ] `index.blade.php` bersih — hanya berisi include partial dan routing role (~25 baris)
- [ ] Semua 10 partial terbuat sesuai Section 4.1
- [ ] Controller menyuplai semua variabel yang dibutuhkan, tidak ada query di blade
- [ ] Migration, model, controller, views, seeder Pengumuman selesai
- [ ] Menu Pengumuman muncul di sidebar untuk Admin Internal
- [ ] Widget `_pengumuman_widget` tampil dengan data dari seeder
- [ ] Semua edge case di Section 10 ter-handle tanpa error
- [ ] Tidak ada N+1 query (cek dengan Laravel Debugbar atau log query)
- [ ] Tidak ada breaking change pada fitur lain
- [ ] Test login sebagai Admin UPP → dashboard UPP tampil
- [ ] Test login sebagai Admin Internal → dashboard Internal tampil