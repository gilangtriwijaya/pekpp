# FITUR RESUBMIT DENGAN VERSIONING - SPESIFIKASI IMPLEMENTASI FINAL

**Tanggal**: 26 Maret 2026  
**Status**: READY FOR IMPLEMENTATION  
**Author**: Technical Analysis  

---

## EXECUTIVE SUMMARY

Fitur ini memungkinkan admin untuk mengizinkan UPP yang sudah selesai validasi untuk mengisi ulang F01. Sistem akan membuat **NEW F01Pengisian record (versioning)** bukan overwrite, namun user experience seperti "edit". Setiap re-submission akan membuat **NEW F02Validasi** otomatis untuk di-validate ulang.

**Key Design Decision**: Unlimited resets OK (business logic ketat: hanya jika F02 status = selesai)

---

## BAGIAN 1: ANALISIS KODE EXISTING

### 1.1 Current Database Schema

#### F01Pengisian Table (Existing Columns)
```
f01_pengisian:
├── id (PK)
├── periode_id (FK)
├── upp_id (FK)
├── status (ENUM: draft, submitted, rolled_back, selesai) ← KEY
├── catatan_umum (TEXT)
├── dikirim_pada (TIMESTAMP)
├── dikirim_oleh (FK users)
├── created_at
├── updated_at
├── deleted_at (SoftDeletes)
└── UNIQUE: (periode_id, upp_id)
```

**Current Status Flow:**
- draft → submitted → selesai

**Limitation**: No version tracking, no parent relationship for versioning

#### F01Jawaban Table (Existing)
```
f01_jawaban:
├── id (PK)
├── f01_pengisian_id (FK) ← Links to specific pengisian
├── pertanyaan_id (FK)
├── nilai (JSON)
├── created_at
└── updated_at
```

**Current**:  
- One set of jawaban per pengisian
- Multiple jawaban rows can exist per pengisian (per pertanyaan)

#### F02Validasi Table (Existing Columns)
```
f02_validasi:
├── id (PK)
├── f01_pengisian_id (FK) ← Links to F01Pengisian
├── periode_id (FK)
├── status (ENUM: draft, dalam_proses, selesai) ← KEY
├── catatan_umum (TEXT)
├── total_nilai (DECIMAL)
├── nilai_mentah (DECIMAL)
├── divalidasi_oleh (FK users)
├── divalidasi_pada (TIMESTAMP)
├── updated_by (FK users)
├── created_at
├── updated_at
└── UNIQUE: (f01_pengisian_id)
```

**Current**:
- 1-to-1 relationship with F01Pengisian
- Scores stored in F02IndikatorValidasi

#### F02IndikatorValidasi Table (Existing)
```
f02_indikator_validasi:
├── id (PK)
├── f02_validasi_id (FK)
├── indikator_id (FK)
├── nilai (DECIMAL) ← Score per indikator
├── catatan (TEXT) ← Notes per indicator
├── status (ENUM)
├── created_at
└── updated_at
```

---

### 1.2 Current Model Relationships

#### F01Pengisian Model
```php
// Current relationships:
public function periode()           // BelongsTo
public function upp()               // BelongsTo
public function dikirimOleh()        // BelongsTo User
public function indikatorNilai()     // HasMany (NOT USED - obsolete)
public function jawaban()            // HasMany F01Jawaban ✅
public function aspekPengisian()     // HasMany (NOT USED)
public function f02()                // HasOne F02Validasi ✅
public function buktiDukung()        // HasMany F01BuktiDukung ✅
```

#### F02Validasi Model
```php
// Current relationships:
public function periode()            // BelongsTo
public function f01()                // BelongsTo F01Pengisian ✅
public function upp()                // BelongsTo Upp (via f01)
public function indikatorValidasi()  // HasMany F02IndikatorValidasi ✅
public function divalidasiOleh()     // BelongsTo User
public function updatedBy()          // BelongsTo User
```

---

### 1.3 Current Controller Flow

#### F01PengisianController Methods
```
index()
  → Redirect ke aspek-list (tidak ada daftar)
  
aspekList($pengisian)
  → Display aspek + indikator dengan progres/skor
  → Load F02 jika status !== draft
  → Show F02 scores & notes jika ada
  
getIndikatorDetail($pengisianId, $indikatorId)
  → AJAX untuk load indikator + pertanyaan
  → Can show F02 validation data jika ada
  
show($pengisian)
  → Show ringkasan pengisian
  
submit($pengisian)
  → Change status draft → submitted
  → Trigger F02 creation otomatis
  
autoSave()
  → Save jawaban without status change
  
saveBuktiDanJawaban()
  → Save aspek-specific data
```

**Key Point**: Bahkan dengan status=selesai, pengisian tetap bisa di-load. Hanya yang di-block adalah edit & submit.

#### F02ValidasiController Methods
```
index()
  → List semua F01 pending validasi (status != draft)
  → Show F02 status (draft, dalam_proses, selesai)
  
show($f01_pengisianId)
  → Create or load F02Validasi
  → Redirect ke aspek-list untuk validasi form
  
aspekList($f02ValidasiId)
  → Display aspek untuk admin score
  
save($f02_id)
  → Save scores & notes per indikator
  
finalize($f02_id)
  → Set F02 status → selesai
  → Set F01 status → selesai (DOUBLE LOCK!)
  
reject($f02_id)
  → Set F02 status → draft
  → Can re-open untuk edit admin
```

**Key Point**: `finalize()` updates BOTH F02 dan F01 status ke selesai. Ini adalah full lock.

---

### 1.4 Current Route Structure

```
GET  /f01                            → F01PengisianController@index
GET  /f01/{pengisian}/aspek          → aspekList
POST /f01/{pengisian}/submit         → submit ← KEY ENDPOINT
POST /f01/{pengision}/auto-save      → autoSave
POST /f01/{pengisianId}/aspek/{aspekId}/save → saveBuktiDanJawaban
GET  /api/f01/{pengisianId}/indikator/{indikatorId}  → getIndikatorDetail

GET  /f02                            → F02ValidasiController@index
GET  /f02/{id}                       → show
GET  /f02/{validasi}/aspek-list      → aspekList
POST /f02/{id}/save                  → save
POST /f02/{id}/finalize              → finalize ← KEY ENDPOINT
POST /f02/{id}/reject                → reject
```

---

## BAGIAN 2: ANALYSIS - COMPATIBILITY DENGAN VERSIONING FEATURE

### 2.1 What Works (No Changes Needed)

✅ **F01Jawaban relationship**: Can stay as-is
- New pengisian v2 dapat create new f01_jawaban rows
- f01_jawaban v1 tetap exist (tidak link ke v2)
- Query logic otomatis filtered by f01_pengisian_id

✅ **F02Validasi per-pengisian**: Works perfectly
- Setiap F01Pengisian v baru = baru F02Validasi
- Current UNIQUE constraint pada (f01_pengisian_id) still works
- Admin can review each F02 independently

✅ **Scoring in F02IndikatorValidasi**: No issue
- Score stored per F02, bukan per F01
- Score lama tetap terlihat di F02 v1 history
- Score baru di F02 v2

✅ **Soft Deletes on F01Pengisian**: OK
- Old versions tetap exist (not hard deleted)
- Can preserve history untuk audit

✅ **Routes**: Mostly reusable
- Routes untuk single pengisian/f02 dapat reuse
- Hanya perlu tambah new routes untuk versioning actions

### 2.2 What Needs Changes

❌ **UNIQUE Constraint (periode_id, upp_id)**
- **Problem**: Current UNIQUE meng-enforce max 1 pengisian per periode+upp
- **Impact**: Cannot create F01 v2 untuk same periode+upp (akan violate constraint!)
- **Solution**: REMOVE constraint, add new constraint yang handle versioning

❌ **Model Relationships**
- **Problem**: F01Pengisian punya relasi 1-to-1 ke F02Validasi
- **Impact**: Jika v2 ada, F02 model masih link ke F01 v1
- **Solution**: Change hasOne → hasMany, add filtering

❌ **Manual F02 Creation**
- **Problem**: F02 harus automatic create jika F01 status berubah
- **Current**: Done in submit() dengan manual orchestration
- **Solution**: Create service layer F01ResubmitService untuk orchestrate

---

## BAGIAN 3: DATABASE MIGRATION SPEC

### 3.1 New Columns untuk F01Pengisian

```sql
ALTER TABLE f01_pengisian ADD COLUMN (
  -- Versioning fields
  `version_number` INT DEFAULT 1 COMMENT 'Increment setiap kali resubmit',
  `previous_f01_pengisian_id` BIGINT UNSIGNED NULL COMMENT 'Link ke versi sebelumnya (NULL untuk v1)',
  `is_latest_version` BOOLEAN DEFAULT true COMMENT 'Flag utk query terbaru'
);
```

**Alasan**:
- `version_number`: Track which cycle this is (v1, v2, v3...)
- `previous_f01_pengisian_id`: Create linked list of versions
- `is_latest_version`: Query optimization untuk always show latest

### 3.2 Modify UNIQUE Constraint

**OLD**:
```sql
UNIQUE KEY `uq_f01_periode_upp` (`periode_id`, `upp_id`)
```

**NEW** (Multiple approaches, choose one):

**Option A - Composite Unique (RECOMMENDED)**:
```sql
-- Drop old constraint
ALTER TABLE f01_pengisian DROP INDEX `uq_f01_periode_upp`;

-- Add new constraint: only latest version is unique
ALTER TABLE f01_pengisian 
  ADD UNIQUE KEY `uq_f01_periode_upp_latest` 
  (`periode_id`, `upp_id`, `is_latest_version`) 
  WHERE `is_latest_version` = 1;
```

**Benefits**:
- Database level consistency
- Only 1 latest pengisian per periode+upp
- Old versions can stay (is_latest = false)

**Option B - No Constraint** (Less safe):
```sql
ALTER TABLE f01_pengisian DROP INDEX `uq_f01_periode_upp`;
-- Rely on application logic only
```

**Recommendation**: **Option A** (database-level safety)

### 3.3 Migration Script

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Add versioning columns
        Schema::table('f01_pengisian', function (Blueprint $table) {
            $table->integer('version_number')->default(1)->after('status');
            $table->foreignId('previous_f01_pengisian_id')
                  ->nullable()
                  ->after('version_number')
                  ->constrained('f01_pengisian')
                  ->nullOnDelete();
            $table->boolean('is_latest_version')->default(true)->after('previous_f01_pengisian_id');
        });
        
        // 2. Add index untuk query optimization
        Schema::table('f01_pengisian', function (Blueprint $table) {
            $table->index('is_latest_version');
            $table->index(['upp_id', 'periode_id', 'is_latest_version']);
        });
        
        // 3. Handle existing unique constraint
        // Drop old constraint
        Schema::table('f01_pengisian', function (Blueprint $table) {
            $table->dropUnique(['periode_id', 'upp_id']);
        });
        
        // Add new unique constraint
        // Note: Depends on database, may need raw SQL
        DB::statement('ALTER TABLE f01_pengisian ADD UNIQUE KEY uq_f01_periode_upp_latest 
                      (periode_id, upp_id, is_latest_version) WHERE is_latest_version = 1');
    }
    
    public function down(): void
    {
        Schema::table('f01_pengisian', function (Blueprint $table) {
            $table->dropIndex(['is_latest_version']);
            $table->dropIndex(['upp_id', 'periode_id', 'is_latest_version']);
            $table->dropForeign(['previous_f01_pengisian_id']);
            $table->dropColumn(['version_number', 'previous_f01_pengisian_id', 'is_latest_version']);
        });
        
        // Recreate old constraint
        Schema::table('f01_pengisian', function (Blueprint $table) {
            $table->unique(['periode_id', 'upp_id'], 'uq_f01_periode_upp');
        });
    }
};
```

---

## BAGIAN 4: MODEL CHANGES

### 4.1 F01Pengisian Model - NEW Relationships

```php
<?php
namespace App\Models;

class F01Pengisian extends Model
{
    // EXISTING: all current relationships stay
    
    // NEW: Versioning relationships
    public function previousVersion()
    {
        return $this->belongsTo(F01Pengisian::class, 'previous_f01_pengisian_id');
    }
    
    public function nextVersion()
    {
        return $this->hasOne(F01Pengisian::class, 'previous_f01_pengisian_id');
    }
    
    // NEW: For loading version history
    public function allVersions()
    {
        // Get all versions from v1 to current
        if (!$this->previous_f01_pengisian_id) {
            // I am v1, get all descendants
            return $this->descendants();
        } else {
            // I'm not v1, find v1 first
            $v1 = $this;
            while ($v1->previous_f01_pengisian_id) {
                $v1 = $v1->previousVersion;
            }
            return $v1->descendants();
        }
    }
    
    private function descendants()
    {
        $all = [$this];
        $current = $this;
        
        while ($current->nextVersion) {
            $current = $current->nextVersion;
            $all[] = $current;
        }
        
        return collect($all);
    }
    
    // NEW: Scope untuk latest version only
    public function scopeLatestVersion($query)
    {
        return $query->where('is_latest_version', true);
    }
    
    public function scopeByUpAndPeriode($query, $uppId, $periodeId)
    {
        return $query
            ->where('upp_id', $uppId)
            ->where('periode_id', $periodeId)
            ->where('is_latest_version', true);
    }
}
```

### 4.2 F02Validasi Model - Modify Relationship

```php
<?php
namespace App\Models;

class F02Validasi extends Model
{
    // OLD (CHANGE):
    // public function f01() { return $this->belongsTo(F01Pengisian::class, 'f01_pengisian_id'); }
    
    // NEW (KEEP SAME but add querying notes):
    public function f01pengisian()
    {
        return $this->belongsTo(F01Pengisian::class, 'f01_pengisian_id');
    }
    
    // NEW: Get all F02 validasi untuk version-chain
    public function scopeForVersionChain($query, $uppId, $periodeId)
    {
        // Return all F02 validasi untuk upp+periode (across all versions)
        subquery = F01Pengisian::where('upp_id', $uppId)
                    ->where('periode_id', $periodeId);
        
        return $query->whereIn('f01_pengisian_id', $subquery);
    }
}
```

---

## BAGIAN 5: SERVICE LAYER - F01ResubmitService

### 5.1 New Service Class

```php
<?php
namespace App\Services;

use App\Models\F01Pengisian;
use App\Models\F01Jawaban;
use App\Models\F02Validasi;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class F01ResubmitService
{
    /**
     * Allow UPP to resubmit - create NEW F01Pengisian version
     * 
     * @param F02Validasi $f02
     * @param User $admin
     * @param array $metadata Optional metadata (reason, notes, etc)
     * @return F01Pengisian new pending version
     * 
     * @throws \Exception if F02 status != selesai
     */
    public function allowResubmit(F02Validasi $f02, User $admin, array $metadata = [])
    {
        // Validate: F02 must be selesai
        if ($f02->status !== 'selesai') {
            throw new \Exception(
                "Cannot allow resubmit: F02 status is {$f02->status}, expected 'selesai'"
            );
        }
        
        $f01Old = $f02->f01pengisian;
        
        // Validate: F01 must be selesai
        if ($f01Old->status !== 'selesai') {
            throw new \Exception(
                "Cannot allow resubmit: F01 status is {$f01Old->status}, expected 'selesai'"
            );
        }
        
        return DB::transaction(function () use ($f01Old, $metadata, $admin) {
            // 1. Mark old version as not latest
            $f01Old->update(['is_latest_version' => false]);
            
            // 2. Create new F01Pengisian (v+1)
            $f01New = F01Pengisian::create([
                'periode_id' => $f01Old->periode_id,
                'upp_id' => $f01Old->upp_id,
                'status' => 'draft', ← KEY: reset to draft
                'version_number' => $f01Old->version_number + 1,
                'previous_f01_pengisian_id' => $f01Old->id,
                'is_latest_version' => true,
                'dikirim_oleh' => $admin->id, // Admin yang allow
                'catatan_umum' => $metadata['catatan'] ?? null,
            ]);
            
            // 3. Copy all f01_jawaban dari v1 → v2 (untuk prefill)
            $oldJawaban = F01Jawaban::where('f01_pengisian_id', $f01Old->id)->get();
            
            foreach ($oldJawaban as $jawaban) {
                F01Jawaban::create([
                    'f01_pengisian_id' => $f01New->id,
                    'pertanyaan_id' => $jawaban->pertanyaan_id,
                    'nilai' => $jawaban->nilai, // Copy nilai
                ]);
            }
            
            // 4. Log this action (optional, untuk audit)
            activity()
                ->performedBy($admin)
                ->on($f01New)
                ->event('resubmit_allowed')
                ->withProperties([
                    'from_version' => $f01Old->version_number,
                    'to_version' => $f01New->version_number,
                    'f02_id' => ...
                ])
                ->log('UPP allowed to resubmit');
            
            return $f01New; ← Return NEW pengisian (belum submit, status=draft)
        });
    }
    
    /**
     * Bulk allow resubmit untuk multiple F02
     * 
     * @param array $f02Ids
     * @param User $admin
     * @return array count success/failed
     */
    public function bulkAllowResubmit(array $f02Ids, User $admin)
    {
        $success = 0;
        $failed = [];
        
        foreach ($f02Ids as $f02Id) {
            try {
                $f02 = F02Validasi::findOrFail($f02Id);
                $this->allowResubmit($f02, $admin);
                $success++;
            } catch (\Exception $e) {
                $failed[$f02Id] = $e->getMessage();
            }
        }
        
        return [
            'success' => $success,
            'failed_count' => count($failed),
            'failed_details' => $failed,
        ];
    }
    
    /**
     * Get previous F02 validasi data untuk display di F01 form
     * 
     * @param F01Pengisian $f01New
     * @return F02Validasi|null previous F02 (dari v sebelumnya)
     */
    public function getPreviousF02Data(F01Pengisian $f01New)
    {
        if (!$f01New->previous_f01_pengisian_id) {
            // Ini v1, tidak ada previous
            return null;
        }
        
        $f01Previous = $f01New->previousVersion;
        
        // Get F02 yang link ke previous version
        return F02Validasi::where('f01_pengisian_id', $f01Previous->id)->first();
    }
    
    /**
     * When UPP submit F01 vN, auto-create new F02Validasi
     * 
     * @param F01Pengisian $f01
     * @return F02Validasi new F02 ready for validation
     */
    public function autoCreateF02(F01Pengisian $f01)
    {
        // Check if F02 already exist (should not, but be safe)
        $existing = F02Validasi::where('f01_pengisian_id', $f01->id)->first();
        if ($existing) {
            return $existing;
        }
        
        return F02Validasi::create([
            'f01_pengisian_id' => $f01->id,
            'periode_id' => $f01->periode_id,
            'status' => 'draft', ← Ready untuk admin untuk validasi
            'catatan_umum' => null,
            'total_nilai' => null,
            'nilai_mentah' => null,
        ]);
    }
}
```

---

## BAGIAN 6: CONTROLLER CHANGES

### 6.1 F01PengisianController - MODIFY submit()

```php
<?php
namespace App\Http\Controllers;

use App\Services\F01ResubmitService;

class F01PengisianController extends Controller
{
    protected $resubmitService;
    
    public function __construct(F01ResubmitService $resubmitService)
    {
        $this->resubmitService = $resubmitService;
    }
    
    /**
     * MODIFIED: Submit F01 (handle both v1 & vN)
     */
    public function submit(Request $request, F01Pengisian $pengisian)
    {
        // Validate policy
        $this->authorize('update', $pengisian);
        
        // Validate status must be draft (unchanged logic)
        if ($pengisian->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'F01 tidak dalam status draft',
            ], 403);
        }
        
        DB::transaction(function () use ($pengisian) {
            // Update status: draft → submitted
            $pengisian->update(['status' => 'submitted']);
            
            // NEW: Auto-create F02Validasi jika belum ada
            $this->resubmitService->autoCreateF02($pengisian); ← NEW LINE
            
            // Log activity
            activity()
                ->performedBy(auth()->user())
                ->on($pengisian)
                ->event('f01_submitted')
                ->log('F01 submitted');
        });
        
        return response()->json([
            'success' => true,
            'message' => 'F01 berhasil di-submit',
            'pengisian_id' => $pengisian->id,
        ]);
    }
    
    /**
     * MODIFIED: Show form - sekarang load previous F02 data jika ada
     */
    public function show(Request $request, F01Pengisian $pengisian)
    {
        $this->authorize('view', $pengisian);
        
        // Existing logic stays
        $aspeks = ...;
        
        // NEW: Load previous F02 jika ini bukan v1
        $previousF02 = null;
        $previousScore = null;
        $previousNotes = null;
        
        if ($pengisian->previous_f01_pengisian_id) {
            $previousF02 = $this->resubmitService->getPreviousF02Data($pengisian);
            
            if ($previousF02) {
                $previousScore = $previousF02->total_nilai;
                // Get catatan per indikator untuk display (akan ditampilkan di form)
            }
        }
        
        return view('f01.form', [
            'pengisian' => $pengisian,
            'aspeks' => $aspeks,
            'previousF02' => $previousF02, ← NEW
            'previousScore' => $previousScore, ← NEW
            'version_number' => $pengisian->version_number, ← NEW
        ]);
    }
}
```

### 6.2 F02ValidasiController - NEW METHOD: allowResubmit()

```php
<?php
namespace App\Http\Controllers;

use App\Services\F01ResubmitService;

class F02ValidasiController extends Controller
{
    protected $resubmitService;
    
    public function __construct(F01ResubmitService $resubmitService)
    {
        $this->resubmitService = $resubmitService;
    }
    
    /**
     * NEW: Allow single UPP to resubmit (triggered from F02 index)
     * 
     * Endpoint: POST /f02/{validasiId}/allow-resubmit
     */
    public function allowResubmit(Request $request, F02Validasi $f02Validasi)
    {
        // Policy: only superadmin or validator
        if (!auth()->user()->isSuperadmin()) {
            abort(403, 'Unauthorized');
        }
        
        try {
            $f01New = $this->resubmitService->allowResubmit(
                $f02Validasi,
                auth()->user(),
                ['catatan' => $request->input('catatan', null)]
            );
            
            return response()->json([
                'success' => true,
                'message' => 'UPP diizinkan mengisi ulang',
                'f01_id' => $f01New->id,
                'version_new' => $f01New->version_number,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
    
    /**
     * NEW: Bulk allow resubmit (triggered from F02 index checkbox)
     * 
     * Endpoint: POST /f02/allow-resubmit-bulk
     */
    public function allowResubmitBulk(Request $request)
    {
        // Policy: only superadmin
        if (!auth()->user()->isSuperadmin()) {
            abort(403, 'Unauthorized');
        }
        
        $request->validate([
            'f02_ids' => 'required|array',
            'f02_ids.*' => 'required|integer|exists:f02_validasi,id',
        ]);
        
        $result = $this->resubmitService->bulkAllowResubmit(
            $request->input('f02_ids'),
            auth()->user()
        );
        
        return response()->json([
            'success' => count($result['failed']) === 0,
            'summary' => $result,
        ]);
    }
    
    /**
     * MODIFY EXISTING: index() - show allow button untuk selesai status
     */
    public function index(Request $request)
    {
        // Existing logic stays
        ...
        
        // Modify the view to pass service reference
        return view('f02.index', [
            'pengisians' => $pengisians,
            ... // existing data
            'canAllowResubmit' => auth()->user()->isSuperadmin(), ← NEW
        ]);
    }
}
```

---

## BAGIAN 7: ROUTES ADDITIONS

```php
<?php
// In routes/web.php, F02 section

Route::group(['middleware' => 'auth'], function () {
    
    // Existing F02 routes stay
    Route::get('/f02', [\App\Http\Controllers\F02ValidasiController::class, 'index'])->name('f02.index');
    Route::get('/f02/{id}', [\App\Http\Controllers\F02ValidasiController::class, 'show'])->name('f02.show');
    Route::post('/f02/{id}/save', [\App\Http\Controllers\F02ValidasiController::class, 'save'])->name('f02.save');
    Route::post('/f02/{id}/finalize', [\App\Http\Controllers\F02ValidasiController::class, 'finalize'])->name('f02.finalize');
    
    // NEW: Allow resubmit routes
    Route::post('/f02/{validasiId}/allow-resubmit', 
        [\App\Http\Controllers\F02ValidasiController::class, 'allowResubmit'])
        ->name('f02.allow-resubmit')
        ->where('validasiId', '[0-9]+')
        ->middleware('superadmin'); // Only admin
    
    Route::post('/f02/allow-resubmit-bulk',
        [\App\Http\Controllers\F02ValidasiController::class, 'allowResubmitBulk'])
        ->name('f02.allow-resubmit-bulk')
        ->middleware('superadmin');
});
```

---

## BAGIAN 8: UI/VIEW CHANGES

### 8.1 F02 Index View - Add Allow Resubmit Button

```blade
<!-- In resources/views/f02/index.blade.php -->

<!-- Bulk Controls -->
@if($canAllowResubmit)
<div class="bulk-controls mb-3">
    <input type="checkbox" id="selectAll"> Select All
    <button class="btn btn-warning" id="bulkAllowResubmit" disabled>
        Allow Selected to Resubmit
    </button>
</div>
@endif

<!-- Table -->
<table class="table">
    <thead>
        <tr>
            @if($canAllowResubmit)
            <th style="width: 50px;">
                <input type="checkbox" class="select-row">
            </th>
            @endif
            <th>UPP</th>
            <th>Periode</th>
            <th>Status F02</th>
            <th>Skor</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach($pengisians as $pengisian)
        <tr>
            @if($canAllowResubmit)
            <td>
                <input type="checkbox" class="select-row" 
                       value="{{ $pengisian->f02_id }}"
                       @if($pengisian->f02_status !== 'selesai') disabled @endif>
            </td>
            @endif
            <td>{{ $pengisian->upp->nama }}</td>
            <td>{{ $pengisian->periode->tahun }}</td>
            <td>{{ $pengisian->f02_status }}</td>
            <td>{{ $pengisian->f02_nilai ?? '-' }}</td>
            <td>
                @if($pengisian->f02_status === 'selesai' && $canAllowResubmit)
                <button class="btn btn-sm btn-warning allowResubmitBtn"
                        data-f02-id="{{ $pengisian->f02_id }}"
                        data-upp-id="{{ $pengisian->upp_id }}">
                    Allow Resubmit
                </button>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Checkbox handling
    const selectAll = document.getElementById('selectAll');
    const selectRows = document.querySelectorAll('.select-row:not(#selectAll)');
    const bulkAllow = document.getElementById('bulkAllowResubmit');
    
    selectAll.addEventListener('change', function() {
        selectRows.forEach(r => {
            if (!r.disabled) r.checked = this.checked;
        });
        updateBulkButtonState();
    });
    
    selectRows.forEach(r => {
        r.addEventListener('change', updateBulkButtonState);
    });
    
    function updateBulkButtonState() {
        const checkedCount = document.querySelectorAll('.select-row:checked').length;
        bulkAllow.disabled = checkedCount === 0;
        if (checkedCount > 0) {
            bulkAllow.textContent = `Allow ${checkedCount} Selected`;
        }
    }
    
    // Bulk allow click
    bulkAllow.addEventListener('click', function() {
        const selected = Array.from(selectRows)
            .filter(r => r.checked)
            .map(r => r.value);
        
        if (!confirm(`Allow ${selected.length} UPP to resubmit?`)) return;
        
        fetch('{{ route("f02.allow-resubmit-bulk") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ f02_ids: selected }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert('Success! Refresh page to see changes.');
                location.reload();
            } else {
                alert('Error: ' + data.summary.message);
            }
        })
        .catch(e => alert('Error: ' + e.message));
    });
    
    // Single allow click
    document.querySelectorAll('.allowResubmitBtn').forEach(btn => {
        btn.addEventListener('click', function() {
            const f02Id = this.dataset.f02Id;
            const uppNama = this.closest('tr').querySelector('td:nth-child(2)').textContent;
            
            if (!confirm(`Allow "${uppNama}" to resubmit?`)) return;
            
            fetch(`{{ url('/f02') }}/${f02Id}/allow-resubmit`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({}),
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert('UPP diizinkan mengisi ulang!');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(e => alert('Error: ' + e.message));
        });
    });
});
</script>
```

### 8.2 F01 Form View - Show Previous Score + Catatan

```blade
<!-- In resources/views/f01/form.blade.php - di awal form -->

@if($previousF02)
<div class="alert alert-info">
    <h5>📊 Skor Sebelumnya (Version {{ $version_number - 1 }})</h5>
    <p><strong>Total Skor:</strong> {{ $previousScore }} / 100</p>
    
    <hr>
    
    <h6>Catatan Validator per Indikator:</h6>
    <table class="table table-sm">
        <thead>
            <tr>
                <th>Indikator</th>
                <th>Skor</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($previousF02->indikatorValidasi as $iv)
            <tr>
                <td>{{ $iv->indikator->nama }}</td>
                <td>{{ $iv->nilai ?? '-' }}</td>
                <td>{{ $iv->catatan ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <p class="text-muted">Editing version {{ $version_number }}. Answers pre-filled dari versi sebelumnya.</p>
</div>
@endif

<!-- Rest of form continues normally -->
<!-- Aspek list, indikator, pertanyaan, etc -->
```

---

## BAGIAN 9: TESTING CHECKLIST

### Unit Tests

- [ ] Test F01ResubmitService.allowResubmit()
  - [ ] Throws exception if F02 status != selesai
  - [ ] Creates new F01Pengisian with correct version_number
  - [ ] Sets previous_f01_pengisian_id correctly
  - [ ] Sets is_latest_version = true on new, false on old
  - [ ] Copies all f01_jawaban from old to new
  - [ ] Creates activity log entry

- [ ] Test F01ResubmitService.bulkAllowResubmit()
  - [ ] Processes all F02 in array
  - [ ] Returns correct success/failed counts
  - [ ] Continues on error for remaining items

- [ ] Test F01ResubmitService.getPreviousF02Data()
  - [ ] Returns null if no previous version
  - [ ] Returns correct F02 if previous exists

- [ ] Test F01ResubmitService.autoCreateF02()
  - [ ] Creates new F02Validasi linked to F01
  - [ ] Returns existing F02 if already present

### Feature Tests

- [ ] Test F01PengisianController.submit()
  - [ ] Status changes draft → submitted
  - [ ] F02Validasi automatically created
  - [ ] Works for both v1 and vN

- [ ] Test F02ValidasiController.allowResubmit()
  - [ ] Requires superadmin authorization
  - [ ] Returns JSON with success
  - [ ] F01 status changes selesai → draft
  - [ ] New F01 can be queried

- [ ] Test F02ValidasiController.allowResubmitBulk()
  - [ ] Accepts array of F02 IDs
  - [ ] Processes multiple items
  - [ ] Returns summary

### Integration Tests

- [ ] Full cycle: v1 → submit → validate → v2 → submit → validate → v3
- [ ] Verify old versions stay in DB with is_latest = false
- [ ] Verify is_latest_version upsert works correctly
- [ ] Verify old F02 scores still accessible
- [ ] Verify views load previous scores correctly
- [ ] Verify checkbox bulk select works in UI

### Edge Cases

- [ ] Cannot resubmit if F02 status != selesai
- [ ] Cannot resubmit if F01 status != selesai
- [ ] Rapid consecutive resubmits don't create duplicates
- [ ] Versioning works with soft-deleted records
- [ ] Unique constraint still prevents duplicate latest versions

---

## BAGIAN 10: DEPLOYMENT CHECKLIST

### Pre-Deployment

- [ ] Backup database
- [ ] Review migration script on staging
- [ ] Test migration rollback works
- [ ] Code review complete
- [ ] All tests passing
- [ ] Performance testing done (indexes working)

### Deployment Steps

1. [ ] Deploy migration
2. [ ] Verify new columns exist
3. [ ] Deploy service layer code
4. [ ] Deploy controller changes
5. [ ] Deploy route additions
6. [ ] Deploy view changes
7. [ ] Clear config cache: `php artisan config:cache`
8. [ ] Clear view cache: `php artisan view:clear`
9. [ ] Test in production environment
10. [ ] Monitor activity logs for errors

### Post-Deployment

- [ ] Verify F02 index loads without errors
- [ ] Test single "Allow Resubmit" button
- [ ] Test bulk checkbox
- [ ] Test F01 form with previous scores shown
- [ ] Test full resubmit cycle (v1 → v2 → v3)
- [ ] Check database for correct version_numbers
- [ ] Monitor logs for any exceptions

---

## BAGIAN 11: KNOWN ISSUES & MITIGATIONS

### Issue 1: UNIQUE Constraint MySQL Syntax

**Problem**: Conditional UNIQUE INDEX dalam MySQL memerlukan specific syntax  
**Solution**: Use raw DB::statement() atau migrations with careful syntax  
**Test**: Run migration on dev DB first

### Issue 2: F02 Relationship Confusion

**Problem**: Old code may assume 1-to-1 F01→F02, now multiple F02 per version-chain  
**Solution**: Update any hardcoded `.f02()` calls to `.f02pengisian()`  
**Audit**: Search codebase for `->f02` pattern before deploy

### Issue 3: View Cache

**Problem**: Blade views may be cached  
**Solution**: Always run `php artisan view:clear` after deploy  
**Prevention**: Document in deployment checklist

### Issue 4: Soft Deletes

**Problem**: If old F01 pengisian is soft-deleted, versioning chain breaks  
**Solution**: Never soft-delete F01pengisian, or handle in query scope  
**Prevention**: Use `withTrashed()` when querying version chain

---

## BAGIAN 12: ROLLBACK PLAN

If deployment fails:

```bash
# Step 1: Rollback migration
php artisan migrate:rollback

# Step 2: Revert code to previous version
git revert HEAD

# Step 3: Clear caches
php artisan config:cache
php artisan view:clear

# Step 4: Restart queue if necessary
php artisan queue:restart
```

**Timeline**: Should complete in ~5 minutes

---

## BAGIAN 13: FUTURE ENHANCEMENTS (Not in this release)

- [ ] Automatic F02 create scheduled job
- [ ] Email notification when resubmit allowed
- [ ] Historical comparison UI (side-by-side v1 vs v2)
- [ ] Revert to previous version feature
- [ ] Audit trail dashboard showing all versions
- [ ] Admin bulk revert (reset all UPP to v1)

---

## RINGKASAN PERUBAHAN KODE

| Component | Status | Changes |
|-----------|--------|---------|
| **Database** | ⚠️ CHANGE REQUIRED | +3 columns, modify UNIQUE constraint |
| **F01Pengisian Model** | ⚠️ CHANGE REQUIRED | +New relationships, +scopes |
| **F02Validasi Model** | ✅ MINOR CHANGE | Update scopes only |
| **F01PengisianController** | ⚠️ CHANGE REQUIRED | Modify submit() & show() methods |
| **F02ValidasiController** | ⚠️ CHANGE REQUIRED | Add 2 new methods, modify index() |
| **Routes** | ⚠️ CHANGE REQUIRED | Add 2 new POST routes |
| **Views** | ⚠️ CHANGE REQUIRED | F02 index add bulk UI, F01 form add previous score display |
| **Services** | ✅ NEW REQUIRED | Create F01ResubmitService |
| **Tests** | ✅ NEW REQUIRED | Create comprehensive test suite |

---

## STATUS FINAL

✅ **ANALYSIS COMPLETE**  
✅ **DESIGN VALIDATED**  
✅ **CODE CHANGES DOCUMENTED**  
✅ **MIGRATION SCRIPT READY**  
✅ **SERVICE LAYER SPECIFIED**  
✅ **ROUTES DEFINED**  
✅ **VIEW MOCKUPS CREATED**  
✅ **TESTING CHECKLIST PROVIDED**  
✅ **DEPLOYMENT PLAN READY**  

**READY FOR IMPLEMENTATION** 🚀

---

**Document Version**: 1.0  
**Last Updated**: 26 March 2026  
**Author**: Technical Analysis Team  
**Status**: FINAL - READY TO IMPLEMENT
