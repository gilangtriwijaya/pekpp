# 📝 Menu Pertanyaan (Questions) - Implementation Complete

## Overview
Menu Pertanyaan F01 telah berhasil diimplementasikan dengan UI modern yang konsisten dengan menu Periode dan Aspek. Sistem mendukung 8 tipe pertanyaan berbeda seperti Google Forms.

---

## ✅ Selesai & Terintegrasi

### 1️⃣ Controller
**File:** `/app/Http/Controllers/F01PertanyaanController.php`

**Methods:**
- `index()` - Tampilkan daftar pertanyaan dengan filter indikator
- `store()` - Simpan pertanyaan baru (auto-generate kode dan urutan)
- `show()` - API endpoint untuk detail pertanyaan (JSON response)
- `update()` - Update pertanyaan existing
- `destroy()` - Hapus pertanyaan (validasi: tidak boleh ada jawaban terkait)
- `toggleActive()` - Toggle status aktif/nonaktif
- `reorder()` - Update urutan pertanyaan via drag-reorder

### 2️⃣ Views Created
```
resources/views/f01/pertanyaan/
├── index.blade.php                  # Main page dengan tabel & filter
└── modals/
    ├── create.blade.php             # Form create/edit (shared modal)
    ├── detail.blade.php             # Detail view modal
    └── delete.blade.php             # Delete confirmation modal
```

### 3️⃣ Routes Registered
```
GET|HEAD   admin/f01/pertanyaan              # Index
POST       admin/f01/pertanyaan              # Store
GET|HEAD   admin/f01/pertanyaan/create       # Create form
GET|HEAD   admin/f01/pertanyaan/{id}         # Show (JSON)
PUT|PATCH  admin/f01/pertanyaan/{id}         # Update
DELETE     admin/f01/pertanyaan/{id}         # Destroy
GET|HEAD   admin/f01/pertanyaan/{id}/edit    # Edit form
POST       admin/f01/pertanyaan/reorder      # Reorder via drag
```

### 4️⃣ Models Updated
- `Pertanyaan.php` - Added: `ordered()` scope, `min`/`max` to fillable
- `F01PertanyaanController.php` - Complete CRUD implementation

### 5️⃣ Database Support
Tabel `pertanyaan` sudah memiliki semua field yang diperlukan:
- `id` - Primary key
- `indikator_id` - Foreign key ke indikator
- `kode` - Kode pertanyaan (auto-generated: Q1, Q2, ...)
- `label` - Teks pertanyaan yang ditampilkan
- `tipe_input` - **8 tipe berbeda** (lihat di bawah)
- `opsi_jawaban` - JSON array untuk pilihan (radio, checkbox, select)
- `wajib` - Boolean: apakah pertanyaan wajib dijawab
- `urutan` - Urutan tampilan
- `aktif` - Boolean: pertanyaan aktif atau tidak
- `min` / `max` - Range untuk number/scale types
- `created_at`, `updated_at`, `deleted_at` (soft delete)

---

## 🎯 8 Tipe Pertanyaan Didukung

| Tipe | Label | Icon | Deskripsi | Opsi | Range |
|------|-------|------|-----------|------|-------|
| `text` | Teks Pendek | 📝 | Single line text input | ❌ | ❌ |
| `textarea` | Teks Panjang | 📄 | Multi-line text area | ❌ | ❌ |
| `number` | Angka | 🔢 | Numeric input | ❌ | ✅ |
| `radio` | Pilihan Ganda | ⭕ | Single choice from options | ✅ | ❌ |
| `checkbox` | Pilihan Banyak | ☑️ | Multiple choices possible | ✅ | ❌ |
| `select` | Dropdown | 📋 | Dropdown list (single choice) | ✅ | ❌ |
| `yesno` | Ya/Tidak | ✅ | Binary yes/no question | ❌ | ❌ |
| `skala` | Skala | 📊 | Rating scale (1-10, etc) | ❌ | ✅ |

**Notes:**
- `Opsi` = Pertanyaan memerlukan daftar opsi jawaban
- `Range` = Pertanyaan memerlukan nilai min/max

---

## 🎨 UI Features

### Index Page
- ✅ Tabel responsif dengan 8 kolom data
- ✅ Filter by Indikator (dropdown)
- ✅ Status badge (Aktif/Nonaktif, Wajib/Optional)
- ✅ Drag-reorder via Sortable.js
- ✅ Tombol aksi: Lihat Detail, Edit, Hapus
- ✅ Pagination (50 items per halaman)
- ✅ Clear design konsisten dengan Periode & Aspek

### Create/Edit Modal
- ✅ Indikator selection (dropdown with [kode] label format)
- ✅ Auto-generate kode if empty (Q1, Q2, ...)
- ✅ Pertanyaan text area
- ✅ Tipe Input selector (8 tipe)
- ✅ **Dynamic form fields:**
  - Untuk `number` / `skala`: Tampilkan Min/Max inputs
  - Untuk `radio` / `checkbox` / `select`: Tampilkan opsi inputs
- ✅ Checkbox: Wajib Diisi, Status Aktif
- ✅ Form validation (Blade + JS)

### Detail Modal
- ✅ Read-only view semua field
- ✅ Format opsi dalam list bullets
- ✅ Status badges (Aktif/Nonaktif)
- ✅ Indikator reference link

### Delete Modal
- ✅ Confirmation dialog
- ✅ Warning jika pertanyaan sudah memiliki jawaban
- ✅ One-click delete

---

## 🔄 Workflow

### Create Pertanyaan Baru
```
1. Click "Tambah Pertanyaan" button
2. Modal appears dengan form kosong
3. Select Indikator
4. Input pertanyaan text
5. Select tipe input
6. (If tipe requires) Input opsi/range
7. Check "Wajib Diisi" dan "Aktif" if needed
8. Click "Simpan Pertanyaan"
→ Auto-generate kode & urutan, simpan ke DB
→ Reload page, tampil di tabel
```

### Edit Pertanyaan
```
1. Click tombol Edit di table row
2. System fetch data via API
3. Modal populate dengan existing data
4. Update fields as needed
5. Click "Perbarui Pertanyaan"
→ PUT request ke controller
→ Reload page
```

### Delete Pertanyaan
```
1. Click tombol Trash di table row
2. Confirmation modal appears
3. If has jawaban → Error: "Tidak dapat menghapus..."
4. If no jawaban → Click "Hapus Selamanya"
→ DELETE request
→ Soft delete ke database
→ Reload page
```

### Reorder Pertanyaan
```
1. Drag rows by grip icon ( ⋮⋮ )
2. Drop at new position
3. System auto-POST new order via reorder endpoint
4. Urutan updated instantly in DB
```

---

## 📋 Form Validation

### Server-side (StorePertanyaanRequest)
```php
- indikator_id: required, exists:indikator,id
- kode: required, max:50
- label: required
- tipe_input: required, in:[8 types]
- opsi_jawaban: required for radio/checkbox/select
- min/max: required if tipe_input=number|skala
- wajib: boolean
- aktif: boolean
```

### Client-side (JavaScript)
- Form fields validation
- Empty required fields check
- Conditional fields visibility
- Toast notifications for errors

---

## 🛠️ Helper Functions

**File:** `/app/Helpers/QuestionHelper.php` & `/app/helpers.php`

```php
// Get human-readable label
getQuestionTypeLabel('radio') // "Pilihan Ganda"

// Get all options with labels
getQuestionTypeOptions() // array dengan semua 8 tipe

// Check if requires options
questionRequiresOptions('radio') // true
questionRequiresOptions('text') // false

// Check if requires range
questionRequiresRange('number') // true
questionRequiresRange('text') // false
```

---

## 🧩 Components Used (Reusable)

**From Component Library:**
- CSS: `components.crud-table.css` - Styling
- JS: `components.crud-table.js` - Modal/form handling
- Traits: `openModal()`, `closeModal()`, `showToast()`

**External Libraries:**
- Sortable.js - Drag-reorder functionality

---

## 📝 Database Queries

### Insert Baru
```sql
INSERT INTO pertanyaan 
(indikator_id, kode, label, tipe_input, opsi_jawaban, wajib, urutan, aktif, created_at, updated_at)
VALUES (1, 'Q1', 'Pertanyaan...', 'text', null, 0, 1, 1, now(), now())
```

### dengan Opsi (Radio/Checkbox)
```sql
INSERT INTO pertanyaan 
(indikator_id, kode, label, tipe_input, opsi_jawaban, ...)
VALUES (1, 'Q1', '...', 'radio', 
  JSON_ARRAY(
    JSON_OBJECT('label', 'Opsi 1', 'value', 'opsi_1'),
    JSON_OBJECT('label', 'Opsi 2', 'value', 'opsi_2')
  ), ...)
```

### dengan Range (Number/Skala)
```sql
INSERT INTO pertanyaan 
(indikator_id, kode, label, tipe_input, min, max, ...)
VALUES (1, 'Q2', '...', 'number', 1, 100, ...)
```

---

## 🔐 Security & Permissions

- ✅ Middleware check: only superadmin, admin_organisasi, admin_bagian_organisasi
- ✅ Soft delete implemented
- ✅ Relationship validation: prevent delete if has jawaban
- ✅ CSRF token validation
- ✅ Form request validation

---

## 📊 Integration Points

### With Indikator
- Each pertanyaan MUST belong to 1 indikator
- Filter table by indikator
- Display indikator kode in table

### With F01Jawaban
- Prevent delete if pertanyaan has jawaban
- API response in JSON format

### With F01PengisianFlow
- Pertanyaan displayed to user during F01 pengisian
- User answers in F01Jawaban table
- Link: Pertanyaan → F01Jawaban (one-to-many)

---

## 🚀 Next Steps (Optional Enhancements)

1. **Bulk Operations**
   - Select multiple & bulk delete
   - Bulk activate/deactivate

2. **Import/Export**
   - Export pertanyaan to Excel
   - Import from Excel template

3. **Template Library**
   - Save as template
   - Load from template

4. **Validasi Rules**
   - Regex pattern for text/textarea
   - Custom validation per jenis

5. **Analytics**
   - Show jawaban rate per pertanyaan
   - Pie chart for radio/checkbox answers

---

## 📞 Troubleshooting

### Issue: Modal tidak muncul
- **Fix:** Clear cache `php artisan view:clear`
- Check browser console for JS errors

### Issue: Reorder tidak berfungsi
- **Fix:** Ensure Sortable.js is loaded in components.crud-table.js
- Check network tab for POST request

### Issue: Helper function undefined
- **Fix:** Run `composer dump-autoload`
- Check if helpers.php in autoload of composer.json

### Issue: Opsi tidak tersimpan
- **Fix:** Ensure opsi_jawaban is collected correctly in JavaScript
- Check Network tab → Payload for FormData

---

## ✨ Design Consistency

**Matches Periode & Aspek UI:**
- ✅ Same header style (emoji + title)
- ✅ Same card design for filter
- ✅ Same table styling & badges
- ✅ Same modal design & colors
- ✅ Same pagination
- ✅ Same button styles
- ✅ Same toast notifications

---

## 📂 File Summary

```
Created/Modified:
├── app/Http/Controllers/F01PertanyaanController.php    [Updated]
├── app/Helpers/QuestionHelper.php                      [Created]
├── app/Models/Pertanyaan.php                           [Updated]
├── app/helpers.php                                     [Created]
├── app/Providers/AppServiceProvider.php               [Updated]
├── resources/views/f01/pertanyaan/
│   ├── index.blade.php                                [Updated]
│   └── modals/
│       ├── create.blade.php                           [Created]
│       ├── edit.blade.php                             [Created]
│       ├── detail.blade.php                           [Created]
│       └── delete.blade.php                           [Created]
├── composer.json                                       [Updated]
└── routes/web.php                                      [Updated]
```

---

## ✅ Testing Checklist

- [x] Create pertanyaan baru dengan berbagai tipe
- [x] Edit pertanyaan existing
- [x] Delete pertanyaan tanpa jawaban
- [x] Drag-reorder pertanyaan
- [x] Filter by indikator
- [x] View detail pertanyaan
- [x] Toggle aktif/nonaktif (optional feature)
- [x] Validate form inputs
- [x] Check AJAX responses
- [x] Verify Database entries

---

**Status:** ✅ **COMPLETE & READY FOR PRODUCTION**

**UI Design:** Modern, Responsive, Consistent ✨
**Functionality:** Full CRUD + Advanced Features 🚀
**Testing:** Tested & Working ✓

