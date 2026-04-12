# Fitur Pertanyaan Bersyarat (Conditional Questions)

## Deskripsi Fitur

Fitur ini memungkinkan pengguna untuk menambahkan pertanyaan lanjutan yang akan ditampilkan secara kondisional berdasarkan jawaban dari pertanyaan jenis "Ya/Tidak". 

### Karakteristik:
- Pertanyaan lanjutan hanya tersedia untuk jenis pertanyaan "Ya/Tidak" (yesno)
- Setiap pertanyaan lanjutan dapat dikonfigurasi untuk tampil jika jawaban: Ya, Tidak, atau Keduanya
- Pertanyaan lanjutan tetap bagian dari grup yang sama (kesatuan) dengan pertanyaan induk
- Saat mengedit pertanyaan induk, pertanyaan lanjutan dapat diperbarui atau dihapus

---

## Implementasi Teknis

### 1. Database Schema

**Migrasi**: `database/migrations/2026_02_14_075549_add_conditional_questions_to_pertanyaan.php`

Kolom baru pada tabel `pertanyaan`:
```sql
- parent_pertanyaan_id (unsignedBigInteger, nullable)
  └─ Referensi ke pertanyaan induk (self-referencing foreign key)
  
- show_when (enum: 'ya', 'tidak', 'keduanya')
  └─ Kapan pertanyaan lanjutan akan ditampilkan
  
- FOREIGN KEY: CASCADE DELETE
  └─ Saat pertanyaan induk dihapus, semua pertanyaan lanjutan juga terhapus
```

### 2. Model Relationships

**File**: `app/Models/Pertanyaan.php`

```php
// Relasi ke pertanyaan induk (jika ini adalah pertanyaan lanjutan)
public function parentQuestion()
{
    return $this->belongsTo(Pertanyaan::class, 'parent_pertanyaan_id');
}

// Relasi ke pertanyaan lanjutan (jika ini adalah pertanyaan yesno)
public function conditionalQuestions()
{
    return $this->hasMany(Pertanyaan::class, 'parent_pertanyaan_id')
                ->orderBy('urutan', 'asc');
}
```

**Fillable Fields**:
```php
'parent_pertanyaan_id', 'show_when'
```

### 3. Backend Implementation

**File**: `app/Http/Controllers/F01PertanyaanController.php`

#### Method: `store()`
- Menyimpan pertanyaan induk terlebih dahulu
- Jika `tipe_input === 'yesno'`, panggil `saveConditionalQuestions()`

#### Method: `update()`
- Update data pertanyaan induk
- Jika `tipe_input === 'yesno'`: hapus pertanyaan lanjutan lama, simpan yang baru
- Jika tipe diubah menjadi bukan yesno: hapus semua pertanyaan lanjutan

#### Method: `saveConditionalQuestions()` (Private)
```php
private function saveConditionalQuestions(Pertanyaan $parent, $request)
```

**Proses**:
1. Ekstrak dari request: `conditional_label[]`, `conditional_tipe[]`, `conditional_show_when[]`
2. Loop melalui label yang tidak kosong
3. Buat record `Pertanyaan` baru dengan:
   - `parent_pertanyaan_id`: Referensi ke pertanyaan induk
   - `indikator_id`: Inherit dari induk
   - `label`: Dari input
   - `tipe_input`: Dari input
   - `show_when`: Dari input (ya/tidak/keduanya)
   - `kode`: Auto-generate format `parent_kode-index`
   - `urutan`: Sequential index
   - `aktif`, `wajib`: Default values (true, false)

#### Method: `show()`
Diupdate untuk meload `conditionalQuestions` relationship:
```php
$pertanyaan->load(['indikator', 'conditionalQuestions']);
```

### 4. Frontend Implementation

#### Modal Form: `resources/views/f01/pertanyaan/modals/create.blade.php`

**Bagian baru: Conditional Questions Section**
- ID: `pertanyaan-conditionalGroup` (hidden by default)
- Hanya terlihat saat `tipe_input === 'yesno'`
- Container untuk pertanyaan lanjutan: `id="pertanyaan-conditionalContainer"`
- Tombol: "Tambah Pertanyaan Lanjutan"

#### JavaScript Functions: `resources/views/f01/pertanyaan/index.blade.php`

**1. `addConditionalQuestion()`**
- Menambahkan baris form untuk pertanyaan lanjutan
- Struktur untuk setiap item:
  - Textarea untuk label pertanyaan
  - Select untuk jenis input
  - Select untuk kondisi tampil (Ya/Tidak/Keduanya)
  - Tombol Hapus

**2. `removeConditionalQuestion(idx)`**
- Menghapus baris pertanyaan lanjutan spesifik berdasarkan ID

**3. `clearConditionalQuestions()`**
- Clear semua pertanyaan lanjutan dan reset counter

**4. `updateTipeInputUI(selectId)`**
- Menampilkan/menyembunyikan conditional group berdasarkan tipe input
- Menampilkan hanya jika tipe === 'yesno'

**5. `loadConditionalQuestions(conditionalQuestions)`**
- Digunakan saat mengedit pertanyaan yang sudah ada
- Populate form dengan data pertanyaan lanjutan yang tersimpan

#### Form Submission: `submitData()`

**Update**:
- Ekstrak data pertanyaan lanjutan dari form
- Append ke FormData sebagai:
  - `conditional_label[]`
  - `conditional_tipe[]`
  - `conditional_show_when[]`

---

## Data Flow

### Membuat Pertanyaan Baru dengan Conditional Questions

```
1. User pilih tipe "Ya/Tidak" 
   → Conditional section muncul

2. User input pertanyaan induk
   → Fill: label, kode, urutan, etc.

3. User klik "Tambah Pertanyaan Lanjutan"
   → Form row ditambahkan

4. User input pertanyaan lanjutan
   → Fill: label, tipe_input, show_when

5. User klik "Simpan Pertanyaan"
   → Submit form dengan data conditional questions

6. Backend:
   a. Validasi data
   b. Simpan pertanyaan induk → dapatkan ID
   c. Loop conditional data
   d. Simpan setiap pertanyaan lanjutan dengan parent_pertanyaan_id
```

### Mengedit Pertanyaan Dengan Conditional Questions

```
1. User klik Edit pada pertanyaan yesno
   → Modal buka, form dipopulate
   → show() method load conditionalQuestions relationship

2. loadConditionalQuestions() dipanggil
   → Populate form dengan data lanjutan yang existing

3. User bisa:
   - Ubah pertanyaan lanjutan
   - Hapus pertanyaan lanjutan
   - Tambah pertanyaan lanjutan baru
   - Ubah tipe induk ke non-yesno → hapus semua lanjutan

4. User klik "Simpan"
   → Submit form

5. Backend (update method):
   a. Update pertanyaan induk
   b. Jika tipe === 'yesno':
      - Hapus pertanyaan lanjutan lama
      - Simpan pertanyaan lanjutan baru
   c. Jika tipe !== 'yesno':
      - Hapus semua pertanyaan lanjutan
```

---

## Validasi

### Backend Validation (StorePertanyaanRequest)

Conditional questions form inputs:
```php
'conditional_label.*' => 'nullable|string|max:1000',
'conditional_tipe.*' => 'nullable|in:text,number,textarea,radio,checkbox,select',
'conditional_show_when.*' => 'nullable|in:ya,tidak,keduanya'
```

### Frontend Validation

- HTML5 required attributes pada conditional label dan tipe input
- Validasi server-side akan menangani error case

---

## Sistem Penomoran (Kode)

Pertanyaan lanjutan menggunakan format kode:
```
Pertanyaan Induk: Q1
Pertanyaan Lanjutan 1: Q1-1
Pertanyaan Lanjutan 2: Q1-2
Pertanyaan Lanjutan 3: Q1-3
```

---

## Testing Checklist

- [x] Database migration executed successfully
- [x] Model relationships defined correctly
- [x] Frontend form UI renders conditional section
- [x] JavaScript functions work for add/remove/clear
- [x] Form submission collects conditional data
- [x] Backend saveConditionalQuestions() method implemented
- [x] Update method handles conditional questions
- [x] Show method loads conditional questions for edit
- [x] All PHP files pass syntax validation
- [ ] Manual integration test: Create pertanyaan with conditional questions
- [ ] Manual integration test: Edit pertanyaan with conditional questions
- [ ] Manual integration test: Change type to non-yesno and verify deletion
- [ ] Manual integration test: Verify database relationships

---

## File Changes Summary

| File | Changes | Status |
|------|---------|--------|
| `database/migrations/2026_02_14_075549_add_conditional...` | Migration file created | ✅ |
| `app/Models/Pertanyaan.php` | Added fillable, parentQuestion(), conditionalQuestions() | ✅ |
| `app/Http/Controllers/F01PertanyaanController.php` | Updated store(), update(), show(), added saveConditionalQuestions() | ✅ |
| `resources/views/f01/pertanyaan/modals/create.blade.php` | Added conditional section UI | ✅ |
| `resources/views/f01/pertanyaan/index.blade.php` | Added JS functions for conditional questions, updated form logic | ✅ |

---

## Usage Notes

1. **Pertanyaan lanjutan adalah bagian dari grup yang sama** - Tidak membuat grup baru, tetap under grup induk

2. **Urutan pertanyaan lanjutan** - Urutan diisi otomatis (1, 2, 3...) berdasarkan input order

3. **Indikator** - Pertanyaan lanjutan inherit indikator dari pertanyaan induk

4. **Akses ke pertanyaan lanjutan** - Via `$pertanyaan->conditionalQuestions()` relationship

5. **Kolom show_when** - Digunakan di frontend saat menampilkan pertanyaan (logic di responden/penilaian)

---

## Future Enhancements

1. Display conditional hierarchy dalam listing view
2. Nested conditional questions (3+ level depth)
3. Advanced condition logic (AND/OR/NOT)
4. Conditional questions untuk tipe input lain (radio, select, etc.)
5. UI untuk managing conditional questions langsung dari listing
