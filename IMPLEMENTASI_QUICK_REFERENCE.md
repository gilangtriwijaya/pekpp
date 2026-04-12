# QUICK REFERENCE: IMPLEMENTASI REVISION WORKFLOW

**Status**: Implementation Ready  
**Complexity**: Medium  
**Estimated Effort**: 4-6 weeks  
**Priority**: High  

---

## RINGKASAN PERUBAHAN YANG DIPERLUKAN

### 1️⃣ DATABASE MIGRATION

```php
// migration: 2026_XX_XX_xxxxxx_add_revision_workflow.php

Schema::table('f01_pengisian', function (Blueprint $table) {
    $table->integer('submission_round')->default(1)->after('status');
    $table->unsignedBigInteger('previous_f01_pengisian_id')->nullable()->after('submission_round');
    $table->timestamp('revision_requested_at')->nullable()->after('previous_f01_pengisian_id');
    $table->unsignedBigInteger('revision_requested_by')->nullable()->after('revision_requested_at');
    $table->text('revision_reason')->nullable()->after('revision_requested_by');
    $table->text('revision_notes')->nullable()->after('revision_reason');
    $table->enum('approval_status', ['pending','approved','rejected','needs_revision'])->default('pending')->after('revision_notes');
    
    $table->foreign('previous_f01_pengisian_id')->references('id')->on('f01_pengisian')->nullOnDelete();
    $table->foreign('revision_requested_by')->references('id')->on('users')->nullOnDelete();
});

Schema::table('f02_validasi', function (Blueprint $table) {
    $table->enum('approval_status', ['pending','approved','rejected','needs_revision'])->default('pending')->after('status');
    $table->text('approval_feedback')->nullable()->after('approval_status');
    $table->timestamp('approved_at')->nullable()->after('approval_feedback');
    $table->unsignedBigInteger('approved_by')->nullable()->after('approved_at');
    
    $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
});

Schema::create('f01_pengisian_revisions', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('f01_pengisian_id');
    $table->integer('round_number');
    $table->string('status_from', 50)->nullable();
    $table->string('status_to', 50)->nullable();
    $table->string('action', 100);
    $table->unsignedBigInteger('action_by')->nullable();
    $table->text('notes')->nullable();
    $table->timestamps();
    
    $table->foreign('f01_pengisian_id')->references('id')->on('f01_pengisian')->cascadeOnDelete();
    $table->foreign('action_by')->references('id')->on('users')->nullOnDelete();
    $table->index(['f01_pengisian_id', 'round_number']);
});
```

### 2️⃣ MODEL UPDATES

**app/Models/F01Pengisian.php**
```php
<?php
namespace App\Models;

class F01Pengisian extends Model
{
    protected $fillable = [
        'periode_id', 'upp_id', 'status', 'dikirim_pada', 'dikirim_oleh',
        'catatan_umum', 'deleted_at',
        // NEW FIELDS
        'submission_round', 'previous_f01_pengisian_id', 'revision_requested_at',
        'revision_requested_by', 'revision_reason', 'revision_notes', 'approval_status'
    ];

    // Relationship to previous submission round
    public function previousVersion()
    {
        return $this->belongsTo(F01Pengisian::class, 'previous_f01_pengisian_id');
    }

    // Relationship to revision requester
    public function revisionRequestedBy()
    {
        return $this->belongsTo(User::class, 'revision_requested_by');
    }

    // Relationship to revisions history
    public function revisions()
    {
        return $this->hasMany(F01PengisianRevision::class);
    }

    // Accessor: Is this a revision round?
    public function isRevisionRound(): bool
    {
        return $this->submission_round > 1;
    }

    // Check if can be edited
    public function canEdit(): bool
    {
        return in_array($this->status, ['draft', 'needs_revision']);
    }

    // Check if can be resubmitted after revision
    public function canResubmit(): bool
    {
        return $this->status === 'needs_revision' 
            && $this->submission_round < 3
            && $this->isWithinRevisionDeadline();
    }

    // Check if within 7-day revision deadline
    public function isWithinRevisionDeadline(): bool
    {
        if (!$this->revision_requested_at) return false;
        return now()->diffInDays($this->revision_requested_at) < 7;
    }
}
```

**app/Models/F01PengisianRevision.php** (NEW)
```php
<?php
namespace App\Models;

class F01PengisianRevision extends Model
{
    protected $table = 'f01_pengisian_revisions';
    protected $fillable = [
        'f01_pengisian_id', 'round_number', 'status_from', 'status_to',
        'action', 'action_by', 'notes'
    ];

    public function pengisian()
    {
        return $this->belongsTo(F01Pengisian::class);
    }

    public function actionBy()
    {
        return $this->belongsTo(User::class, 'action_by');
    }
}
```

### 3️⃣ SERVICE LAYER

**app/Services/F01RevisionService.php** (NEW)
```php
<?php
namespace App\Services;

use App\Models\F01Pengisian;
use App\Models\F01PengisianRevision;
use Illuminate\Support\Facades\DB;

class F01RevisionService
{
    public function requestRevision(F01Pengisian $pengisian, array $data, User $requestedBy)
    {
        return DB::transaction(function () use ($pengisian, $data, $requestedBy) {
            // Validate status
            if (!in_array($pengisian->status, ['submitted', 'selesai'])) {
                throw new \Exception('Revision hanya bisa diminta dari status submitted/selesai');
            }

            // Validate max rounds
            if ($pengisian->submission_round >= 3) {
                throw new \Exception('Max submission rounds (3) sudah tercapai');
            }

            // Update pengisian
            $pengisian->update([
                'status' => 'needs_revision',
                'approval_status' => 'needs_revision',
                'revision_requested_at' => now(),
                'revision_requested_by' => $requestedBy->id,
                'revision_reason' => $data['reason'] ?? null,
                'revision_notes' => $data['notes'] ?? null,
            ]);

            // Create audit entry
            F01PengisianRevision::create([
                'f01_pengisian_id' => $pengisian->id,
                'round_number' => $pengisian->submission_round,
                'status_from' => 'submitted',
                'status_to' => 'needs_revision',
                'action' => 'revision_requested',
                'action_by' => $requestedBy->id,
                'notes' => $data['reason'] ?? null,
            ]);

            return $pengisian->fresh();
        });
    }

    public function resubmitAfterRevision(F01Pengisian $pengisian, User $actionBy)
    {
        return DB::transaction(function () use ($pengisian, $actionBy) {
            // Validate
            if ($pengisian->status !== 'needs_revision') {
                throw new \Exception('Status harus needs_revision');
            }

            if (!$pengisian->isWithinRevisionDeadline()) {
                throw new \Exception('Revision deadline sudah lewat');
            }

            // Update pengisian
            $pengisian->update([
                'status' => 'revised_submitted',
                'submission_round' => $pengisian->submission_round + 1,
                'approval_status' => 'pending',
            ]);

            // Create audit entry
            F01PengisianRevision::create([
                'f01_pengisian_id' => $pengisian->id,
                'round_number' => $pengisian->submission_round - 1, // Previous round
                'status_from' => 'needs_revision',
                'status_to' => 'revised_submitted',
                'action' => 'revised_submitted',
                'action_by' => $actionBy->id,
            ]);

            return $pengisian->fresh();
        });
    }

    public function approveFinal(F01Pengisian $pengisian, F02Validasi $f02, User $approvedBy)
    {
        return DB::transaction(function () use ($pengisian, $f02, $approvedBy) {
            // Update F02
            $f02->update([
                'approval_status' => 'approved',
                'approved_by' => $approvedBy->id,
                'approved_at' => now(),
                'status' => 'selesai',
            ]);

            // Update F01
            $pengisian->update([
                'status' => 'selesai_final',
                'approval_status' => 'approved',
            ]);

            // Create audit entry
            F01PengisianRevision::create([
                'f01_pengisian_id' => $pengisian->id,
                'round_number' => $pengisian->submission_round,
                'status_from' => 'selesai',
                'status_to' => 'selesai_final',
                'action' => 'approved_final',
                'action_by' => $approvedBy->id,
            ]);

            return $pengisian->fresh();
        });
    }

    public function getRevisionHistory(F01Pengisian $pengisian)
    {
        return F01PengisianRevision::where('f01_pengisian_id', $pengisian->id)
            ->orderBy('created_at', 'desc')
            ->with('actionBy')
            ->get();
    }

    public function compareRounds(F01Pengisian $current, ?F01Pengisian $previous = null)
    {
        $prev = $previous ?? $current->previousVersion;
        if (!$prev) return null;

        $currentAnswers = DB::table('f01_jawaban')
            ->where('f01_pengisian_id', $current->id)
            ->pluck('nilai', 'pertanyaan_id');

        $prevAnswers = DB::table('f01_jawaban')
            ->where('f01_pengisian_id', $prev->id)
            ->pluck('nilai', 'pertanyaan_id');

        $changes = [];
        foreach ($currentAnswers as $qId => $current) {
            $prevVal = $prevAnswers[$qId] ?? null;
            if ($current !== $prevVal) {
                $changes[] = [
                    'pertanyaan_id' => $qId,
                    'before' => $prevVal,
                    'after' => $current,
                ];
            }
        }

        return $changes;
    }
}
```

### 4️⃣ CONTROLLER UPDATES

**app/Http/Controllers/F02ValidasiController.php** (Method Addition)
```php
public function requestRevision(Request $request, $id)
{
    $validated = $request->validate([
        'reason' => 'required|string|min:10',
        'notes' => 'required|string|min:20',
    ]);

    try {
        $f02 = F02Validasi::findOrFail($id);
        $pengisian = $f02->pengisian;
        
        $revisionService = app(F01RevisionService::class);
        $revisionService->requestRevision($pengisian, $validated, auth()->user());

        return response()->json([
            'success' => true,
            'message' => 'Revision request sent to user',
            'data' => ['pengisian_id' => $pengisian->id]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 422);
    }
}

public function approveFinal(Request $request, $id)
{
    try {
        $f02 = F02Validasi::findOrFail($id);
        $pengisian = $f02->pengisian;
        
        $revisionService = app(F01RevisionService::class);
        $revisionService->approveFinal($pengisian, $f02, auth()->user());

        return response()->json([
            'success' => true,
            'message' => 'Pengisian approved and locked',
            'data' => $pengisian
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 422);
    }
}
```

**app/Http/Controllers/F01PengisianController.php** (Method Addition)
```php
public function resubmitAfterRevision(Request $request, F01Pengisian $pengisian)
{
    $this->authorize('update', $pengisian);

    if ($pengisian->status !== 'needs_revision') {
        abort(403, 'Pengisian tidak dalam status needs_revision');
    }

    try {
        $revisionService = app(F01RevisionService::class);
        
        // Save the baru answers first (same as normal submit)
        $this->saveAnswers($request, $pengisian);
        
        // Then mark as resubmitted
        $revisionService->resubmitAfterRevision($pengisian, auth()->user());

        return redirect()->route('f01.show', $pengisian->id)
            ->with('success', 'Jawaban berhasil diperbarui dan di-resubmit');
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
    }
}
```

### 5️⃣ ROUTES

```php
// routes/web.php

// F01 Re-submission
Route::post('/f01/{pengisian}/resubmit-after-revision', [F01PengisianController::class, 'resubmitAfterRevision'])
    ->name('f01.resubmit-after-revision');

// F02 Revision Request
Route::post('/f02/{id}/request-revision', [F02ValidasiController::class, 'requestRevision'])
    ->name('f02.request-revision');

// F02 Final Approval
Route::post('/f02/{id}/approve-final', [F02ValidasiController::class, 'approveFinal'])
    ->name('f02.approve-final');
```

---

## TESTING CHECKLIST

- [ ] Status transitions work correctly
- [ ] Max 3 rounds enforced
- [ ] 7-day deadline enforced
- [ ] Revision history tracked correctly
- [ ] Compare rounds functionality
- [ ] Permissions checked at each step
- [ ] Audit trail complete
- [ ] DB transactions working
- [ ] Email notifications sent
- [ ] UI shows revision status correctly

---

## ROLLOUT PLAN

**Phase 1**: Deploy schema changes + models (no UI changes yet)  
**Phase 2**: Deploy services + controllers (behind feature flag)  
**Phase 3**: Enable feature flag for pilot UPPs (2-3 UPPs)  
**Phase 4**: Gather feedback + fix bugs  
**Phase 5**: Enable for all UPPs

---

## MONITORING & METRICS

Track:
- Submissions per round (should decrease with each round)
- Time between revision request and resubmit
- Admin revision request frequency
- User satisfaction with feedback

---

*Implementation Ready*: Yes  
*Backward Compatible*: Yes  
*Feature Flaggable*: Yes  
*Risk Level*: Low-Medium  
