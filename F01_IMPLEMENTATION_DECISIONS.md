# F01 Penilaian - Implementation Decisions & Technical Spec

**Date**: 14 February 2026  
**Status**: Architecture Finalized - Ready for Development

---

## 1️⃣ Database Strategy

### Using Existing Tables ✅

We're using existing F01 system tables (no new tables needed):

```
f01_pengisian          → Master form submission (1 per periode + upp)
  ├─ id, periode_id, upp_id
  ├─ status (draft | submitted | rolled_back)
  └─ timestamps
  
f01_jawaban            → Individual answers (many per pengisian)
  ├─ id, f01_pengisian_id (FK)
  ├─ pertanyaan_id (FK)
  ├─ nilai (JSON - can store string, array, or complex objects)
  └─ timestamps

f01_indikator_nilai    → Score calculations per indicator
  └─ Calculated from jawaban, not direct input
```

### API Endpoints Needed

**POST /api/f01/pengisian**
- Create draft pengisian (or get if exists)
- Return: `{ id, periode_id, upp_id, status, jawaban: [...] }`

**POST /api/f01/submit**
- Receive all cached answers
- Bulk insert/update F01Jawaban
- Calculate F01IndikatorNilai
- Change pengisian status to 'submitted'

**GET /api/f01/{pengisianId}**
- For admin view (read-only)
- Return all data with relationships

---

## 2️⃣ Browser Cache Strategy

### Storage: localStorage (Session-scoped)

**Key Format**: `f01_penilaian_{periodeId}_{userId}`

**Structure**:
```javascript
{
  periodeId: 1,
  userId: 5,
  pengisianId: 10,           // Reference to DB record
  createdAt: "2026-02-14T10:00:00Z",
  lastUpdated: "2026-02-14T10:30:00Z",
  
  answers: {
    indikator_1: {
      pertanyaan_1: {
        value: "ya",
        tipe_input: "yesno",
        answered: true,
        timestamp: "2026-02-14T10:05:00Z"
      },
      pertanyaan_2: {
        value: null,
        answered: false,
        skipped: false
      }
    }
  },
  
  validation: {
    aspek_1: { valid: true, errors: {} },
    aspek_2: { valid: false, errors: { pertanyaan_5: "Wajib diisi" } }
  },
  
  status: "draft"           // draft | submitted | readonly
}
```

### Cache Lifecycle

```
Page Load:
  ↓
Check localStorage for existing cache
  ├─ If found & valid → Load it
  └─ If not → Create from DB
  ↓
User fills form → Auto-save to localStorage (every input change)
  ↓
User navigates → Load from cache (no server calls)
  ↓
User submits → POST all to server, clear cache
  ↓
Session ends → Browser clears localStorage automatically
```

### Expiry: Session-based

**Clears when:**
- ✓ User logs out from application (explicit logout)
- ✓ Session cookie expires (server-side)
- ✓ Browser tab closes (if using sessionStorage) OR survives (if localStorage)
- ✓ User manually clears browser cache

**Duration**: Persists across browser refresh, multi-tab access to same periode

### Pros & Cons Analysis

**PROS** ✅:
- `Zero server impact`: No database for cache, only final submit
- `Instant feedback`: No network delay for auto-save
- `Offline capable`: Fill form without internet, submit later
- `Mobile friendly`: Works perfectly on slow networks
- `Scalable`: Each user's cache isolated, no server storage
- `Responsive UX`: Next/Previous instant (no server round-trip)

**CONS** ❌:
- `Device-specific`: Cache not available on different device
- `Browser-specific`: IE cache not available in Chrome
- `Storage limit`: ~5-10MB per domain (enough for ~2000+ questions)
- `Manual sync`: Multi-device users see stale data if
  - User fills on PC, hasn't submitted
  - User opens same form on mobile → sees device-local cache (old)
  - Solution: Check DB for latest submission before cache load
- `Accidental loss`: User might clear cache thinking it's safe

**Server Impact**: ✅ **MINIMAL & POSITIVE**
- ✗ No extra database queries (only at final submit)
- ✗ No session memory bloat (cache is client-side only)
- ✗ No bandwidth for auto-save polling
- ✓ Single POST request when user done
- ✓ Reduces server load vs constant form-save architecture

---

## 3️⃣ UI/UX Architecture

### Layout: Aspek Tabs + Indikator Accordion

```
[Aspek 1 Tab] [Aspek 2 Tab] [Summary Tab]
        ↓ (active)
    
▼ Indikator 1.1 (expanded)
  Q1: [input]
  Q2: [input]
  └─ Q2-1: [conditional input]
  
▼ Indikator 1.2 (collapsed, expand on click)
  [click to expand]

▼ Indikator 1.3 (collapsed)
  [click to expand]

[< Prev Aspek] [Next Aspek >]
```

### Navigation & State Management

**Aspek Tab Navigation**:
1. User on Aspek 1, clicks "Next Aspek >"
2. validateAspekAnswers(aspek_1)
   - Loop all indikators in aspek
   - Check all required, non-skipped questions answered
   - Return: { valid: boolean, errors: {...} }
3. If valid:
   - Save answers to cache
   - Switch to Aspek 2 tab
   - Load cached answers for Aspek 2
4. If invalid:
   - Show inline error messages
   - Highlight unanswered questions
   - Stay on Aspek 1

**Indikator Accordion**:
1. User clicks indikator header
2. toggleIndikator() 
   - If collapsed → Slide down, expand
   - If expanded → Slide up, collapse
   - Restore form values from cache

### Error Display: Inline Messages

Show validation errors **below each unanswered question**:

```html
<div class="question-wrapper">
  <label>Pertanyaan wajib diisi *</label>
  <input type="text" />
  
  <!-- Error message (if validation failed) -->
  <div class="error-message">
    <i class="icon-error"></i>
    Pertanyaan ini wajib diisi
  </div>
</div>
```

**Styling**:
- Red text (#d32f2f)
- Small icon + message
- Appears when validation failed + field empty
- Disappears when user starts typing

### Summary Page + Edit Links

Summary shows all answers before final submit:

```
┌─ Aspek 1 (collapsed by default)
  └─ Indikator 1.1
     ├─ Q1: Jawab: Ya [Edit ↑]
     ├─ Q2: Jawab: (kosong) ⚠ [Edit ↑]
     └─ Q3: Jawab: (skipped) [Edit ↑]
```

**Edit Feature**:
- Click [Edit ↑] next to any question
- Auto-switch to appropriate Aspek tab
- Auto-expand Indikator accordion
- Auto-scroll to question
- Auto-focus input field
- Show error message if any

---

## 4️⃣ Conditional Questions & Sequential Skip

### Conditional Questions

Q2 with `show_when: 'ya'` only appears if Q1 answered 'ya':

```javascript
// Check visibility
if (Q1.skip_if_answer && userAns.Q1 === Q1.skip_if_answer) {
  Q2.visible = true;
} else {
  Q2.visible = false;
}
```

**Inline display**: Q2-1 shows slightly indented under Q2 with different styling

### Sequential Skip

If Q1 answered with `skip_if_answer` value → all Q2, Q3, ... in same indikator marked skipped:

```javascript
if (Q1.skip_if_answer && userAns.Q1 === Q1.skip_if_answer) {
  Q2.skipped = true;
  Q3.skipped = true;
  Q4.skipped = true;
  // All shown as: "Tidak perlu diisi (pertanyaan sebelumnya)"
}
```

**Validation**: Skipped questions exempt from required validation

---

## 5️⃣ Auto-Save Behavior

**When**: Every input change (user types, selects option)

```javascript
// Event listeners on all inputs
document.addEventListener('change', (e) => {
  if (e.target.matches('input, select, textarea')) {
    saveAnswerToCache(
      questionId,
      e.target.value
    );
  }
});

function saveAnswerToCache(questionId, value) {
  const cache = loadCache();
  cache.answers[indikatorId][questionId] = {
    value: value,
    answered: (value !== ''),
    timestamp: new Date().toISOString()
  };
  saveCache(cache);
  updateProgressBar();
}
```

**Debounce**: Optional - delay 500ms to avoid too frequent saves
**Server**: Not contacted during auto-save (JSON to localStorage only)

---

## 6️⃣ Admin Read-Only View

**URL**: `/admin/f01/penilaian/{pengisianId}`

**Data Loading**:
1. Fetch F01Pengisian with relationships
2. Load all F01Jawaban
3. Display in same layout but:
   - All inputs disabled (readonly)
   - No navigation buttons
   - Show metadata (created_by, created_at, last_updated)
   - Action buttons: [Approve] [Reject] [Request Revision] [Print]

**No cache needed** (read-only, no editing)

---

## 7️⃣ Implementation Checklist

### Phase 1: API Endpoints
- [ ] POST /api/f01/pengisian - Create or get draft pengisian
- [ ] POST /api/f01/submit - Save all answers to DB
- [ ] GET /api/f01/{pengisianId} - Get pengisian for admin view
- [ ] GET /api/f01/{pengisianId}/answers - Get all jawaban

### Phase 2: Frontend Architecture
- [ ] Tab component for Aspek switching
- [ ] Accordion component for Indikator toggling
- [ ] Form input styling (all  types)
- [ ] Error message styling
- [ ] Progress bar component
- [ ] Summary page layout

### Phase 3: Cache Management
- [ ] Load cache from localStorage
- [ ] Save cache on every input change
- [ ] Restore form state from cache when switching tabs
- [ ] Clear cache on logout
- [ ] Handle cache expiry/session end

### Phase 4: Validation & Logic
- [ ] Per-aspek validation function
- [ ] Conditional questions visibility logic
- [ ] Sequential skip logic
- [ ] Error message display/hide
- [ ] Validation state management in cache

### Phase 5: Special Features
- [ ] Summary page generation
- [ ] Edit button → auto-navigate to question
- [ ] Auto-scroll to question
- [ ] Focus & highlight input
- [ ] Error pulse animation

### Phase 6: Admin Features
- [ ] Read-only view mode
- [ ] Response fetching & display
- [ ] Approve/Reject workflow
- [ ] Print/PDF export

---

## 🎯 Key Technical Decisions Made

| Decision | Choice | Reason |
|----------|--------|--------|
| Storage | localStorage | Session-based, zero server impact |
| Navigation | Aspek Tabs | Clear section grouping, mobile friendly |
| Indikator Layout | Accordion | Space efficient, progressive disclosure |
| Validation | Per-Aspek | Good UX, catch errors before next section |
| Error Display | Inline | User can see exactly what's wrong |
| Database | Existing tables | f01_pengisian + f01_jawaban already exists |
| Submit | Bulk POST | Single request, all answers at once |
| Edit Feature | Auto-scroll + focus | Quick correction without manual search |
| Admin View | Read-only with DB fetch | No cache needed, server source of truth |

---

## 📋 Next Steps

1. **Approve architecture** (this document)
2. **Start Phase 1**: Build API endpoints
3. **Start Phase 2**: Build UI components (parallel)
4. **Phase 3**: Implement cache management
5. **Phase 4**: Validation & conditional logic
6. **Phase 5**: Special features
7. **Phase 6**: Admin interface
8. **Testing & QA**: Cross-browser, performance
9. **Deploy**: Staging → Production

---

**Created**: 14 Feb 2026
**Status**: READY FOR DEVELOPMENT
**Tech**: Laravel (API) + Vanilla JS (Frontend) + localStorage (Cache)
