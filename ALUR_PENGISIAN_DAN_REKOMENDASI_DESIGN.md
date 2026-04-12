# ANALISA ALUR PENGISIAN F01-F02 & REKOMENDASI DESIGN FLEXIBLE RE-SUBMISSION

**Date**: 26 March 2026  
**Analysis**: Flow State Management & Re-submission Workflow  
**Prepared For**: System Architecture Review

---

## BAGIAN 1: ALUR PENGISIAN SAAT INI (CURRENT STATE)

### Status Flow Diagram (Linear/One-Way)

```
F01 PENGISIAN WORKFLOW:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

1. draft (Awal)
   ↓
2. submitted (User klik "Kirim")
   ↓
3. selesai (F02 finalize)
   [LOCKED - tidak bisa edit]

F02 VALIDASI WORKFLOW:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

1. draft (Belum mulai validasi)
   ↓
2. dalam_proses (Admin ngisi nilai)
   ↓
3. selesai (Admin finalize)
   [LOCKED - terbentuk hasil final]
```

### Current Status Distribution
```
F01 Pengisian (48 total):
├─ draft:     23 (47.9%) - Belum submit, user bisa edit
├─ submitted:  7 (14.6%) - Sudah submit, user tidak bisa edit, menunggu F02 validasi
└─ selesai:   18 (37.5%) - F02 sudah finalize, kedua locked

F02 Validasi (25 total):
├─ draft:        2 (8.0%)  - F02 belum mulai validasi
├─ dalam_proses: 5 (20.0%) - F02 sedang di-validasi
└─ selesai:     18 (72.0%) - F02 sudah selesai, hasil final locked
```

---

### Relationship & Dependency Map

```
┌─────────────────────────────────────────────────┐
│               UPP (Organization)                │
│  (1:N) - 1 UPP punya banyak User                │
└────────────────┬────────────────────────────────┘
                 │
                 ├→ F01Pengisian (1:1 per periode)
                 │   ├─ Status: draft/submitted/selesai
                 │   ├─ Columns: periode_id, upp_id, status
                 │   ├─ Timestamps: created_at, updated_at
                 │   └─ Relationship to F02
                 │       │
                 │       └→ F02Validasi (1:1)
                 │           ├─ Status: draft/dalam_proses/selesai
                 │           ├─ Columns: f01_pengisian_id, periode_id, status
                 │           ├─ Fields: divalidasi_oleh, divalidasi_pada, total_nilai
                 │           └─ Relationship to Indikator
                 │               │
                 │               └→ F02IndikatorValidasi (1:N)
                 │                   ├─ Columns: f02_validasi_id, indikator_id
                 │                   ├─ Fields: nilai, catatan, status
                 │                   └─ Status: draft/final
                 │
                 ├→ F01Jawaban (N - per pertanyaan)
                 │   ├─ Columns: f01_pengisian_id, pertanyaan_id, nilai
                 │   ├─ Raw answers stored (editable during draft)
                 │   └─ Cleared on submit
                 │
                 └→ F01BuktiDukung (N - supporting files)
                     ├─ Columns: f01_pengisian_id, indikator_id, url_bukti
                     └─ Locked on submit
```

### CRITICAL FLOW RULES (Current Implementation)

**Rule 1: Submit is One-Way**
```
if ($pengisian->status !== 'draft') {
    abort(403, 'User tidak bisa edit, pengisian sudah submitted');
}
```

**Rule 2: F02 Lock After Finalize**
```
$pengisian->update(['status' => 'selesai']);  // Locks user editing
F02Validasi->update(['status' => 'selesai']); // Final validation locked
```

**Rule 3: F02 Calculation is Immutable**
- Once F02 finalizes: `total_nilai` cannot be changed
- All individual `f02_indikator_validasi` records are frozen

---

## BAGIAN 2: PROBLEMA & LIMITATION SAAT INI

### ❌ Problem 1: No Feedback Loop
```
User Submit F01
    ↓
Admin Validasi & Score F02
    ↓
User gets final score...
    ↓
❌ TAPI USER TIDAK BISA LAKUKAN APAPUN
   - Tidak bisa lihat scoring detail
   - Tidak bisa revisi data salah
   - Tidak bisa submit feedback/klarifikasi
```

### ❌ Problem 2: Admin Error Not Recoverable
```
If Admin enter wrong nilai in F02:
  → F01 status = selesai
  → Tidak bisa edit F01 lagi
  → Tidak bisa rollback F02 validasi
  → Stuck dengan data salah

Current options:
  - Delete & restart (data loss)
  - Manual database fix (risky, tidak teraudit)
```

### ❌ Problem 3: No Revision Workflow
```
Skenario: User buat F01, submit, kemudian ada data baru/klarifikasi diperlukan
  → Harus contact admin
  → Admin manually rollback status
  → User re-submit
  → No formal process/audit trail
```

### ❌ Problem 4: Status Confusion at boundaries
```
F01Pengisian status = 'selesai' but admin could still:
  - Edit F02 validation catatan
  - Recalculate values
  → Status tidak correctly reflect actual "finalization"
```

---

## BAGIAN 3: REKOMENDASI DESIGN - FLEXIBLE RE-SUBMISSION WORKFLOW

### 🎯 RECOMMENDED SOLUTION: Multi-Round Submission Pattern

```
IMPROVED ALUR (WITH FLEXIBILITY):
═════════════════════════════════════════════════════════════════

F01 ROUND 1:
┌──────────────────────────────────┐
│ draft → submitted → selesai       │
│ (User fills & submits)           │
│ (Admin F02 validates)            │
└──────────────────────────────────┘
         ↓
F02 FINALIZE + FEEDBACK:
┌──────────────────────────────────┐
│ Admin review F01 answers         │
│ Admin can mark for REVISION      │
│ Send feedback to user (optional) │
└──────────────────────────────────┘
         ↓
          ├─ Path A: APPROVED (selesai)
          │   └─ Final Lock, User cannot edit
          │
          └─ Path B: NEEDS_REVISION (needs_revision)
              └─ User gets F01 unlocked for re-editing

F01 ROUND 2 (if revision needed):
┌──────────────────────────────────┐
│ User can edit submitted answers  │
│ User re-submit revised data      │
└──────────────────────────────────┘
         ↓
F02 RE-VALIDATE (admin reviews again):
┌──────────────────────────────────┐
│ Admin sees revision history      │
│ Re-validate updated answers      │
│ Approve or ask more revision     │
└──────────────────────────────────┘
         ↓
F02 FINAL APPROVAL:
┌──────────────────────────────────┐
│ Pengisian = 'selesai_final'      │
│ Complete lock (no more edits)    │
└──────────────────────────────────┘
```

---

## BAGIAN 4: DATABASE SCHEMA CHANGES (PROPOSED)

### Add New Columns to f01_pengisian

```sql
-- Add to existing table
ALTER TABLE f01_pengisian ADD COLUMN (
  submission_round INT DEFAULT 1 COMMENT 'Track round number',
  previous_f01_pengisian_id BIGINT UNSIGNED NULLABLE COMMENT 'Link to previous round',
  revision_requested_at TIMESTAMP NULL COMMENT 'When revision requested',
  revision_requested_by BIGINT UNSIGNED NULL COMMENT 'Who requested revision',
  revision_reason TEXT NULL COMMENT 'Why revision needed',
  revision_notes TEXT NULL COMMENT 'Admin notes/feedback for user',
  approval_status ENUM('pending', 'approved', 'rejected', 'needs_revision') DEFAULT 'pending'
) AFTER status;

ALTER TABLE f01_pengisian 
  ADD FOREIGN KEY (revision_requested_by) REFERENCES users(id) ON DELETE SET NULL;
```

### Update f02_validasi for Better Tracking

```sql
ALTER TABLE f02_validasi ADD COLUMN (
  approval_status ENUM('pending', 'approved', 'rejected', 'needs_revision') DEFAULT 'pending',
  approval_feedback TEXT NULL COMMENT 'Admin approval decision & notes',
  approved_at TIMESTAMP NULL,
  approved_by BIGINT UNSIGNED NULL
) AFTER status;

ALTER TABLE f02_validasi 
  ADD FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL;
```

### New Audit Table (Revision History)

```sql
CREATE TABLE f01_pengisian_revisions (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    f01_pengisian_id BIGINT UNSIGNED NOT NULL,
    round_number INT NOT NULL,
    status_from VARCHAR(50),
    status_to VARCHAR(50),
    action VARCHAR(100) COMMENT 'submitted/revision_requested/re_submitted/approved',
    action_by BIGINT UNSIGNED COMMENT 'User who triggered action',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (f01_pengisian_id) REFERENCES f01_pengisian(id),
    FOREIGN KEY (action_by) REFERENCES users(id)
);
```

---

## BAGIAN 5: STATUS ENUM CHANGES

### Current Status Values
```
F01Pengisian: draft | submitted | selesai
F02Validasi:  draft | dalam_proses | selesai
```

### Proposed Status Values (Enhanced)

**F01Pengisian Status**
```
├─ draft                    (User belum submit)
├─ submitted                (User submit, waiting admin approval)
├─ needs_revision           (Admin ask for revision, user can edit again)
├─ revised_submitted        (User re-submit revised data)
├─ selesai                  (Admin approved, LOCKED - not really final)
└─ selesai_final            (F02 completely finalized, truly locked)
```

**F02Validasi Status**
```
├─ draft                    (Belum mulai validasi)
├─ dalam_proses             (Admin ngisi nilai)
├─ pending_approval         (Validation complete, waiting approval)
├─ needs_revision           (Asking user to revise)
├─ selesai                  (Approved, but can reopen if needed)
└─ selesai_final            (Final approval, truly locked)
```

**ApprovaalStatus (New)**
```
├─ pending                  (Awaiting admin decision)
├─ approved                 (Admin approved, locked)
├─ rejected                 (Rejected, need major rework)
└─ needs_revision           (Minor revisions needed)
```

---

## BAGIAN 6: UI/UX FLOW CHANGES

### For User (F01 Pengisian)

**Screen 1: List Pengisian**
```
┌─────────────────────────────────────────┐
│ F01 Daftar Pengisian                    │
├─────────────────────────────────────────┤
│ Periode 2026 - Puskesmas Letung         │
├─────────────────────────────────────────┤
│ Status: needs_revision ⚠️               │
│ Last action: Permintaan Revisi          │
│ Revision Reason: "Bukti untuk Q3 tidak  │
│                   sesuai standar"        │
│                                         │
│ [Edit & Resubmit] [View Feedback]       │
└─────────────────────────────────────────┘
```

**Screen 2: Edit F01 (After Revision Requested)**
```
┌──────────────────────────────────────────┐
│ Edit F01 - Round 2                       │
│ (Revision Requested)                     │
├──────────────────────────────────────────┤
│ Revision Notes from Admin:               │
│ ┌──────────────────────────────────────┐ │
│ │ "Bukti dukung Q3 perlu update dengan│ │
│ │  dokumen terbaru. Upload ulang file" │ │
│ └──────────────────────────────────────┘ │
│                                          │
│ [Q1] [Q2] [Q3 - NEEDS UPDATE] [...]      │
│                                          │
│ Submission Round: 2/3 (Max 3 attempts)   │
│                                          │
│ [Preview] [Resubmit] [Cancel]            │
└──────────────────────────────────────────┘
```

### For Admin (F02 Validasi)

**Screen: Validation Review**
```
┌────────────────────────────────────────┐
│ F02 Validasi - Puskesmas Letung        │
├────────────────────────────────────────┤
│ F01 Status: submitted                  │
│ Revision History:                      │
│  • Round 1: Initial submission         │
│  • Round 2: User resubmit (2 changes)  │
│  • Round 3: Pending review             │
│                                        │
│ [View Change History] [Compare Rounds] │
├────────────────────────────────────────┤
│ Approval Decision:                     │
│ ○ Approve (Final)                     │
│ ○ Request Revision                     │
│   └ Reason: [textarea]                 │
│                                        │
│ [Save & Send to User] [Submit & Lock]  │
└────────────────────────────────────────┘
```

---

## BAGIAN 7: BUSINESS RULES (Re-submission Logic)

### Rule 1: Submission Limits
```php
// Max 3 submission rounds per pengisian
if ($pengisian->submission_round >= 3 && $status === 'needs_revision') {
    abort(403, 'Max submission rounds (3) reached. Contact admin.');
}
```

### Rule 2: Revision Only from Approved/Selesai State
```php
// Only admin can request revision from submitted/selesai states
if (!in_array($pengisian->status, ['submitted', 'selesai'])) {
    abort(403, 'Revisi hanya bisa diminta dari status submitted/selesai');
}
```

### Rule 3: Re-submission Timeline
```php
// User has 7 days to resubmit after revision requested
$daysLeft = now()->diffInDays($pengisian->revision_requested_at->addDays(7));
if ($daysLeft <= 0) {
    abort(403, 'Deadline untuk revisi sudah expired');
}
```

### Rule 4: Comparison & Audit Trail
```php
// Track what changed between rounds
$previousF01 = $pengisian->previousVersion;
$changes = compareF01Rounds($previousF01, $pengisian);
// Log: User changed jawaban pertanyaan 3 from "Ya" to file URL
```

---

## BAGIAN 8: IMPLEMENTATION ROADMAP

### Phase 1: Database Schema (Low Risk)
```
Week 1:
- [ ] Create migration for new columns
- [ ] Create f01_pengisian_revisions table
- [ ] Add indexes for performance
- [ ] Test migration rollback
```

### Phase 2: Model & Service Changes (Medium Risk)
```
Week 2:
- [ ] Update F01Pengisian model with revision relations
- [ ] Create F01RevisionService for tracking
- [ ] Add approval_status mutators
- [ ] Update F01ScoringService to handle revisions
```

### Phase 3: Controller Logic (Medium Risk)
```
Week 3:
- [ ] Add requestRevision() method to F02ValidasiController
- [ ] Add resubmit() method to F01PengisianController
- [ ] Update submit() to track round number
- [ ] Add status transition validation
```

### Phase 4: UI/Views (Low Risk)
```
Week 4:
- [ ] Update F01 list to show revision status
- [ ] Add revision feedback modal
- [ ] Add Edit screen for revision mode
- [ ] Update F02 to show revision history
```

### Phase 5: Testing & UAT (High Priority)
```
Week 5-6:
- [ ] Unit tests for all status transitions
- [ ] Integration tests for revision flow
- [ ] UAT with real users
- [ ] Documentation update
```

---

## BAGIAN 9: ALTERNATIVE DESIGNS (Trade-offs Analysis)

### Option A: Multi-Round (RECOMMENDED)
```
✅ Pros:
  - User can revise without complete restart
  - Admin can provide feedback inline
  - Full audit trail of changes
  - Supports iterative improvement

❌ Cons:
  - More complex state management
  - Requires tracking revisions
  - Database schema more complex
  - Risk of misconfigured permissions
```

### Option B: Rollback-Only (Simple)
```
✅ Pros:
  - Simpler implementation
  - Admin can rollback if mistake
  - No new status values needed

❌ Cons:
  - No user involvement in revision
  - Admin must manually reprocess
  - No feedback loop to user
  - Audit trail unclear
```

### Option C: Parallel Submissions (Not Recommended)
```
Allow multiple concurrent F01 submissions per period
✅ Pros:
  - User can try multiple versions
  
❌ Cons:
  - Which version is official? Confusing
  - Duplicate work for admin
  - Complex business logic
  - Data integrity risk
```

---

## BAGIAN 10: RISK MITIGATION

### Risk 1: Data Integrity During Transitions
```
Mitigation:
- Use database transactions for all status changes
- Validate previous_f01_pengisian_id refers to valid round
- Prevent manual deletion of revision history
```

### Risk 2: Infinite Revision Loop
```
Mitigation:
- Set max_submission_rounds = 3 (configurable)
- Add deadline for resubmission (7 days)
- Admin must explicitly close revision period
```

### Risk 3: Confusion About "Selesai" Status
```
Mitigation:
- Rename to "selesai_pending" (awaiting admin final approval)
- Only use "selesai_final" when truly done
- Add UI badges to clarify state
```

### Risk 4: Backward Compatibility
```
Mitigation:
- Add migration with DEFAULT values
- Old pengisian stay in "selesai" state
- No breaking changes to existing APIs
- Add feature flag to enable/disable
```

---

## BAGIAN 11: APPROVAL WORKFLOW PSEUDOCODE

### User Resubmit Flow
```php
// In F01PengisianController
public function resubmitAfterRevision(Request $request, F01Pengisian $pengisian)
{
    // Validate
    if ($pengisian->status !== 'needs_revision') {
        abort(403, 'Tidak dalam status needs_revision');
    }
    if ($pengisian->submission_round >= 3) {
        abort(403, 'Max submission rounds exceeded');
    }
    
    // Save jawaban (same as normal submit)
    $this->saveAnswers($request, $pengisian);
    
    // Update pengisian
    $pengisian->update([
        'status' => 'revised_submitted',
        'submission_round' => $pengisian->submission_round + 1,
    ]);
    
    // Create audit entry
    F01PengisianRevision::create([
        'f01_pengisian_id' => $pengisian->id,
        'round_number' => $pengisian->submission_round,
        'action' => 'revised_submitted',
        'action_by' => auth()->id(),
        'notes' => $request->input('revision_notes'),
    ]);
}
```

### Admin Request Revision Flow
```php
// In F02ValidasiController
public function requestRevision(Request $request, F02Validasi $f02)
{
    $validated = $request->validate([
        'reason' => 'required|string|min:10',
        'notes' => 'required|string|min:20',
    ]);
    
    $pengisian = $f02->pengisian;
    
    // Mark for revision
    $pengisian->update([
        'status' => 'needs_revision',
        'approval_status' => 'needs_revision',
        'revision_requested_at' => now(),
        'revision_requested_by' => auth()->id(),
        'revision_reason' => $validated['reason'],
        'revision_notes' => $validated['notes'],
    ]);
    
    // Keep F02 in draft state (don't finalize)
    $f02->update([
        'approval_status' => 'needs_revision',
        'approval_feedback' => $validated['notes'],
    ]);
    
    // Create audit entry
    F01PengisianRevision::create([
        'f01_pengisian_id' => $pengisian->id,
        'round_number' => $pengisian->submission_round,
        'action' => 'revision_requested',
        'action_by' => auth()->id(),
        'notes' => $validated['reason'],
    ]);
    
    // Notify user (email/notification)
    $pengisian->upp->users->each(function($user) use ($pengisian) {
        notify($user, 'revision_requested', $pengisian);
    });
}
```

### Admin Approve Final Flow
```php
// In F02ValidasiController
public function approveFinal(F02Validasi $f02)
{
    $pengisian = $f02->pengisian;
    
    // Finalize F02
    $f02->update([
        'status' => 'selesai',
        'approval_status' => 'approved',
        'approved_by' => auth()->id(),
        'approved_at' => now(),
    ]);
    
    // Lock F01 completely
    $pengisian->update([
        'status' => 'selesai_final',
        'approval_status' => 'approved',
    ]);
    
    // Create audit entry
    F01PengisianRevision::create([
        'f01_pengisian_id' => $pengisian->id,
        'round_number' => $pengisian->submission_round,
        'action' => 'approved_final',
        'action_by' => auth()->id(),
    ]);
}
```

---

## BAGIAN 12: COMPARISON TABLE

| Aspect | Current Flow | Recommended Multi-Round |
|--------|-------------|------------------------|
| **User Edits After Submit** | ❌ Not possible | ✅ Possible if revision requested |
| **Admin Feedback** | ❌ No mechanism | ✅ Inline feedback with reason |
| **Revision Limit** | N/A | ✅ 3 rounds max, 7-day deadline |
| **Audit Trail** | ⚠️ Minimal | ✅ Complete revision history |
| **Error Recovery** | ❌ Delete & restart | ✅ Request revision, reprocess |
| **User Experience** | ⚠️ One-shot, stressful | ✅ Iterative, less stressful |
| **Admin Effort** | ⚠️ High (manual fix) | ✅ Low (UI-driven process) |
| **Data Integrity** | ✅ Good | ✅ Better (transaction-based) |
| **Complexity** | ✅ Simple | ⚠️ Medium (manageable) |

---

## REKOMENDASI AKHIR

### 🎯 BEST PRACTICE APPROACH

**Implement Option A (Multi-Round) dengan configuration:**

1. **Core Feature**
   - Enable revision workflow for F01-F02
   - Set max_rounds = 3, deadline = 7 days
   - Track full revision history

2. **Admin Controls**
   - Button "Request Revision" in F02 validation
   - Fill reason + feedback textarea
   - System auto-notifies user

3. **User Experience**
   - Show revision reason prominently
   - Allow max 3 resubmissions
   - Clear messaging about deadline

4. **Safety Measures**
   - All transitions logged to f01_pengisian_revisions
   - Database transactions for atomicity
   - Comparison view between rounds
   - Permission checks at each step

5. **Phase Implementation**
   - Start with schema changes
   - Gradual feature rollout
   - Beta test with 1-2 UPPs first
   - Full UAT before production

---

## CONCLUSION

Current F01-F02 flow adalah **working but rigid**. Implementasi Multi-Round workflow akan:

✅ Reduce user stress (not one-shot deal)  
✅ Give admin proper feedback mechanism  
✅ Provide audit trail untuk compliance  
✅ Support realistic business requirements  
✅ Improve data quality melalui iterative revision  

**Effort**: Medium complexity, ~4-6 weeks implementation  
**Risk**: Low (backward compatible, can be feature-flagged)  
**ROI**: High (user satisfaction, data quality, compliance)

---

*Document Prepared*: 26 March 2026  
*Recommendations Status*: Ready for Architecture Review  
*Next Step*: Stakeholder approval + Sprint planning
