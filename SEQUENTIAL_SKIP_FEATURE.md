# Sequential Skip Condition Feature

## Deskripsi Fitur

Fitur ini memungkinkan admin untuk mengatur kondisi **sequential skip** pada pertanyaan. Jika seorang responden menjawab pertanyaan tertentu dengan opsi yang telah dikonfigurasi, maka **semua pertanyaan berikutnya dalam indikator yang sama akan di-skip (tidak ditampilkan)**.

## Use Case

**Contoh**: Verifikasi Dokumen
```
Indikator: Verifikasi Dokumen
├─ Q1 (urutan 1): "Apakah dokumen tersedia?" → Type: Ya/Tidak
│  └─ IF jawab "Tidak" → SKIP Q2-Q5
├─ Q2 (urutan 2): "Jenis dokumen?" → Type: Dropdown
├─ Q3 (urutan 3): "Tanggal penerbitan?" → Type: Text
├─ Q4 (urutan 4): "Status validasi?" → Type: Radio
└─ Q5 (urutan 5): "Catatan verifikator?" → Type: Textarea
```

**Alur**:
- Jika responden jawab "Tidak" pada Q1 → Q2, Q3, Q4, Q5 tidak ditampilkan
- Jika responden jawab "Ya" pada Q1 → Q2, Q3, Q4, Q5 ditampilkan normal

## Implementasi Teknis

### 1. Database Schema

**Kolom Baru**: `skip_if_answer` (string, nullable)
- Menyimpan nilai jawaban yang akan memicu skip
- Contoh: "tidak", "ya", atau opsi apapun dari radio/select
- NULL = tidak ada skip condition

### 2. Model (Pertanyaan)

```php
protected $fillable = [
    // ... existing fields ...
    'skip_if_answer'  // NEW FIELD
];
```

### 3. Backend Logic

**File**: `F01PertanyaanController.php`

#### Method: `getFilteredQuestions($indikatorId, $answers = [])`
```php
/**
 * Get filtered questions based on skip conditions
 * @param int $indikatorId - ID dari indikator
 * @param array $answers - Assoc array: question_id => answer_value
 * @return Collection of visible questions
 */
public function getFilteredQuestions($indikatorId, $answers = [])
```

**Logika**:
1. Fetch semua pertanyaan aktif dalam indikator (urutan ascending)
2. Loop melalui setiap pertanyaan
3. Jika ada previous question yang `skip_if_answer` cocok dengan user answer
   → Skip semua pertanyaan dengan urutan > previous question
4. Return hanya pertanyaan yang tidak di-skip

#### Method: `shouldSkipQuestion($question, $answers = [])`
```php
/**
 * Determine if a specific question should be skipped
 * @return bool
 */
public function shouldSkipQuestion($question, $answers = [])
```

**Gunakan**: Untuk validasi per-question apakah harus ditampilkan

### 4. Frontend UI

**Location**: `modals/create.blade.php`

**New Section**: "Skip if Answer"
- ID: `pertanyaan-skipGroup`
- Hidden by default, muncul saat tipe input adalah: yesno, radio, checkbox, select
- Dropdown options populasi dinamis berdasarkan:
  - Untuk yesno: [Ya, Tidak]
  - Untuk radio/checkbox/select: Opsi yang ada di form

### 5. Frontend JavaScript

**File**: `index.blade.php`

#### Function: `populateSkipOptions(tipe)`
```javascript
/**
 * Populate skip dropdown based on question type
 * - yesno → [Ya, Tidak]
 * - radio/checkbox/select → ambil dari opsi_jawaban inputs
 */
```

#### Function: `updateSkipDropdown(selectId)`
```javascript
/**
 * Called when skip dropdown changes
 * Bisa diperluas untuk validasi/logging
 */
```

#### Integration: `addOpsiInput()`
- Updated untuk trigger `populateSkipOptions()` saat opsi baru ditambah/dihapus

#### Integration: `populateEditForm()`
- Load `skip_if_answer` value saat edit pertanyaan
- Auto-populate skip dropdown berdasarkan question type

### 6. Validation

**File**: `StorePertanyaanRequest.php`

```php
'skip_if_answer' => ['nullable','string','max:255']
```

**Message**:
```php
'skip_if_answer.string' => 'Skip answer harus berupa teks',
'skip_if_answer.max' => 'Skip answer maksimal 255 karakter',
```

## Data Flow

### Saat Membuat Pertanyaan

```
1. Admin pilih indikator & buat pertanyaan
2. Pilih tipe input (yesno, radio, checkbox, select)
   → "Skip if Answer" section muncul
3. Pilih opsi dari dropdown (auto-populate)
   → Misal: select "Tidak" untuk yesno type
4. Simpan pertanyaan
   → skip_if_answer = "tidak" tersimpan di DB
```

### Saat Responden Mengisi Form

```
1. Responden lihat Q1: "Apakah dokumen tersedia?"
   → Type: yesno
2. Responden jawab: "Tidak"
3. Frontend/Backend check:
   - Q1.skip_if_answer = "tidak"
   - user_answer = "tidak"
   - Match! → SKIP
4. Q2, Q3, Q4, Q5 tidak boleh ditampilkan
```

## cara Menggunakan di Controller/API

```php
// Di controller yang handle form submission
public function getFormQuestions($indikatorId, Request $request)
{
    $answers = $request->input('answers', []); // array of answered questions
    
    // Get filtered questions
    $questions = $this->getFilteredQuestions($indikatorId, $answers);
    
    return response()->json(['questions' => $questions]);
}

// Atau untuk individual check
public function isQuestionVisible($questionId, Request $request)
{
    $question = Pertanyaan::findOrFail($questionId);
    $answers = $request->input('answers', []);
    
    if ($this->shouldSkipQuestion($question, $answers)) {
        return response()->json(['visible' => false]);
    }
    
    return response()->json(['visible' => true]);
}
```

## Fitur Khusus

### 1. Cascade Skip
Jika Q1 → SKIP, tidak hanya Q2 yang skip, tetapi **semua Q2 sampai Qn** yang urutan lebih tinggi dalam indikator yang sama juga skip.

### 2. Conditional + Sequential Compatibility
- Sequential skip system berjalan **independent** dari conditional questions system
- Keduanya bisa coexist tanpa konflik
- Sequential skip check dilakukan sebelum conditional questions check

### 3. Skip untuk Conditional Questions
- Jika parent question ter-skip, maka conditional questions-nya juga tidak ditampilkan
- Logic: Jika parent tidak visible → child juga tidak visible

## Validation Rules

| Field | Rule | Message |
|-------|------|---------|
| skip_if_answer | nullable | Optional field |
| skip_if_answer | string | Harus berupa teks |
| skip_if_answer | max:255 | Maksimal 255 karakter |

## Testing

### Manual Test Scenarios

**Scenario 1: Basic Skip**
```
Setup:
- Q1: Ya/Tidak, skip_if_answer: "tidak"
- Q2: Text input
- Q3: Textarea

Test:
1. Create Q1 dengan skip condition
2. Edit check: dropdown show correct option
3. Answer Q1 with "tidak"
4. Check: Q2, Q3 hidden?
```

**Scenario 2: Multi-Option Skip**
```
Setup:
- Q1: Radio [Lengkap, Kurang, Tidak], skip_if_answer: "tidak"
- Q2-Q5: Various types

Test:
1. Answer "Lengkap" → Q2-Q5 visible
2. Answer "Kurang" → Q2-Q5 visible
3. Answer "Tidak" → Q2-Q5 hidden
```

**Scenario 3: Edit with Skip**
```
Test:
1. Create Q1 dengan skip_if_answer: "tidak"
2. Edit Q1
3. Check: dropdown pre-selected dengan "tidak"
4. Change ke "ya"
5. Save & verify change
```

## Database Migration

**File**: `2026_02_14_081731_add_skip_if_answer_to_pertanyaan.php`

```php
Schema::table('pertanyaan', function (Blueprint $table) {
    $table->string('skip_if_answer')->nullable()->after('show_when')
          ->comment('Jika dijawab dengan value ini, skip semua pertanyaan berikutnya dalam indikator yang sama');
});
```

**Status**: ✅ Executed (22.24ms)

## File Changes

| File | Changes | Status |
|------|---------|--------|
| `2026_02_14_081731_add_skip_if_answer_to_pertanyaan.php` | Migration created | ✅ |
| `app/Models/Pertanyaan.php` | Added 'skip_if_answer' to fillable | ✅ |
| `app/Http/Controllers/F01PertanyaanController.php` | Added getFilteredQuestions(), shouldSkipQuestion() | ✅ |
| `app/Http/Requests/StorePertanyaanRequest.php` | Added validation rules | ✅ |
| `resources/views/f01/pertanyaan/modals/create.blade.php` | Added skip section UI | ✅ |
| `resources/views/f01/pertanyaan/index.blade.php` | Added JS functions, updated addOpsiInput() | ✅ |

## Backward Compatibility

✅ **Fully Backward Compatible**
- skip_if_answer is nullable → existing questions unaffected
- Existing forms work normally
- No breaking changes to API/schema

## Future Enhancements

1. [ ] UI display skip indicator in pertanyaan listing
2. [ ] Batch manage skip conditions
3. [ ] Complex skip logic (OR, AND, multiple conditions)
4. [ ] Skip with specific opsi in radio/select
5. [ ] Visual flow diagram showing skip paths

---

**Status**: 🟢 READY FOR TESTING
**Syntax**: ✅ All files validated
**Cache**: ✅ Cleared and rebuilt
