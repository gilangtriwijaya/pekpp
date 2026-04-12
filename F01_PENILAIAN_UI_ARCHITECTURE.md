# F01 Penilaian - UI/UX Architecture & Implementation Plan

## 🎯 Overview

Sistem penilaian F01 dengan fitur:
- **Accordion + Tabs hybrid layout**: Aspek sebagai Tab, Indikator sebagai Accordion
- **Auto-save cache**: localStorage untuk temporary save jawaban
- **Smart validation**: Per-Aspek, only non-skipped questions
- **Summary review**: Sebelum final submit
- **Admin read-only view**: Untuk validasi

---

## 📐 Visual Architecture

### Layout: Aspek Tabs + Indikator Accordion

```
┌───────────────────────────────────────────────────────────────┐
│ Penilaian F01 - Periode: [Periode Name]              [Info]   │
│ Progress: 25/50 soal ████████░░░░░░░░░░░░ 50%                │
└───────────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────────┐
│ [Aspek 1] [Aspek 2] [Aspek 3] ... [Summary] [Read-only?]    │ ← TAB
└──────────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────────┐
│                                                               │
│ ▼ Indikator 1.1: Kompetensi SDM              ✓ 5/7  [71%]   │ ← Accordion
│   ├─ Q1: Apakah dokumen tersedia? [Radio]    [✓]          │
│   ├─ Q2: Jenis dokumen? [Dropdown]            [ ]          │
│   └─ Q2-1: Detail dokumen? [Text] (conditional) [ ]        │
│                                                               │
│ ▼ Indikator 1.2: Pelatihan SDM                ○ 2/5  [40%]   │
│   ├─ Q3: Sudah dilatih? [Radio]               [ ]          │
│   ├─ Q4: Topik pelatihan? [Textarea]          [✓]          │
│   └─ Q5: Sertifikat? [Radio]                  [✓]          │
│                                                               │
│ ▶ Indikator 1.3: (collapsed)                                │
│                                                               │
│ [< Previous Aspek] [Next Aspek >] [Save Draft]            │
│                                                               │
└──────────────────────────────────────────────────────────────┘
```

---

## 1️⃣ Database Tables Analysis

### Existing F01 Tables (Can be reused!)

**`f01_pengisian`** - Master form submission record
```sql
- id (PK)
- periode_id (FK) → Which appraisal period
- upp_id (FK) → Which organization unit
- status (enum: draft, submitted, rolled_back)
- catatan_umum (text) → General notes
- dikirim_pada (timestamp)
- dikirim_oleh (user_id)
- created_at, updated_at, deleted_at
```

**`f01_jawaban`** - Individual answers
```sql
- id (PK)
- f01_pengisian_id (FK) → Which form submission
- pertanyaan_id (FK) → Which question
- nilai (JSON/TEXT) → Answer value (can store array for multi-select)
- created_at, updated_at
```

### Relationships

```
User
  └─ Periode (Appraisal Period)
     └─ F01Pengisian (Form submission per periode+upp)
        ├─ F01Jawaban (Individual answers)
        │  └─ Pertanyaan (Questions)
        │     └─ Indikator → Aspek
        │
        └─ F01IndikatorNilai (Calculated scores per indicator)
```

### Usage Strategy

**Create/Get Pengisian**:
```php
// When user opens form
$pengisian = F01Pengisian::firstOrCreate(
  ['periode_id' => $periodeId, 'upp_id' => $uppId],
  ['status' => 'draft']
);

// All answers belong to this pengisian
$answers = $pengisian->jawaban()->get();
```

**Save Answer**:
```php
// When user submits (POST from frontend)
F01Jawaban::updateOrCreate(
  ['f01_pengisian_id' => $pengisian->id, 'pertanyaan_id' => $questionId],
  ['nilai' => $value] // Can be string, array, or JSON
);
```

**Fetch for Admin View**:
```php
// Admin viewing responses
$pengisian = F01Pengisian::with('jawaban.pertanyaan', 'periode', 'upp')->find($id);

// Loop through indikators and get jawaban
foreach ($indikators as $indikator) {
  foreach ($indikator->pertanyaan as $question) {
    $answer = $pengisian->jawaban
      ->where('pertanyaan_id', $question->id)
      ->first();
    // Display: $answer->nilai
  }
}
```

---

## 3️⃣ Browser Cache Strategy - localStorage

### Cache Expiry: Session-based

**When cache is cleared:**
1. ✓ User logs out → Session destroyed → Clear cache ✓
2. ✓ Browser tab closes → Can persist with sessionStorage if needed
3. ✓ User manually clears localStorage → Cache gone
4. ✓ New login → Old cache ignored (different user_id)

**Storage Duration**: Survives browser refresh, available until session expired

### Pros & Cons of Browser-side Cache

**PROS** ✅:
- **Zero server impact**: No database/API calls for caching, only reads
- **Instant save**: No network latency for auto-save
- **Offline capable**: User can fill form offline, submit when online
- **Scalable**: Each browser holds own cache, no server storage needed
- **Responsive**: Instant next/previous navigation without server
- **Fail-safe**: If server down, user data still in localStorage

**CONS** ❌:
- **Browser-specific**: Cache not available across devices/browsers
- **Storage limit**: ~5-10MB per domain (usually enough for 1000+ questions)
- **Not synced**: If form submitted from Device A, cached data on Device B is stale
- **Manual cleanup**: Needs JS to clear on logout
- **User clear**: User can accidentally clear cache (localStorage)

**Server Impact**: ✅ **MINIMAL** - Cache only affects browser
- No extra database queries (only at final submit)
- No session memory bloat
- No bandwidth for auto-save (unlike server-side polling)
- Only final POST request hits server

### Cache Structure

```javascript
Key: f01_penilaian_{periodeId}_{userId}

{
  "periodeId": 1,
  "userId": 5,
  "createdAt": "2026-02-14T10:00:00Z",
  "lastUpdated": "2026-02-14T10:30:00Z",
  "answers": {
    // Per Indikator
    "indikator_1": {
      "pertanyaan_1": {
        "value": "ya",
        "tipe_input": "yesno",
        "timestamp": "2026-02-14T10:05:00Z",
        "answered": true
      },
      "pertanyaan_2": {
        "value": null,
        "tipe_input": "radio",
        "timestamp": null,
        "answered": false,
        "skipped": false  // True jika di-skip karena pertanyaan sebelumnya
      },
      "pertanyaan_2_1": {  // Conditional question
        "value": "Surat resmi",
        "tipe_input": "text",
        "timestamp": "2026-02-14T10:05:30Z",
        "answered": true,
        "isConditional": true,
        "parentQuestionId": 2,
        "showWhen": "ya"
      }
    },
    "indikator_2": { /* ... */ }
  },
  "validation": {
    "aspek_1": {
      "valid": false,
      "errors": {
        "pertanyaan_4": "Minimum 10 karakter"
      }
    },
    "aspek_2": { "valid": true, "errors": {} }
  },
  "currentTab": "aspek_1",  // Currently viewing
  "status": "draft"  // draft | submitted | readonly
}
```

---

## 🔄 Navigation Flow

### Tab Navigation (Aspek)

```
User at Aspek 1 Tab
    ↓
Click [Next Aspek >] button
    ↓
validateAspekAnswers(aspek_1)
    ├─ Get all indikator in aspek_1
    ├─ For each indikator
    │  ├─ Get all non-skipped questions
    │  └─ Check if all answered (value !== null)
    └─ Return {valid: boolean, errors: {questionId: message}}
    ↓
[IF validation fails]
    → Show error messages
    → Highlight unanswered questions
    → Stay on current tab
    ↓
[IF validation passes]
    ├─ Save answers to localStorage
    ├─ Update validation object
    ├─ Switch to Aspek 2 Tab
    └─ Load Aspek 2 data from localStorage
```

### Accordion Navigation (Indikator within Tab)

```
User clicks on Indikator accordion header
    ↓
toggleIndikator(id)
    ├─ Check if already expanded
    ├─ If collapsed → expand (slide down animation)
    └─ If expanded → collapse (slide up animation)
    
NOTE: Saat expand, load questions dari cache jika ada
```

### Previous Button

```
User at Aspek 2, click [< Previous Aspek]
    ↓
validateCurrentAspekBeforeLeaving? (optional)
    ├─ If yes → validate & save
    ├─ If no → just switch
    └─ Go back to Aspek 1
    
Load Aspek 1 data from cache
    → Restore all accordion states
    → Restore all answer values
```

---

## 💾 Cache Operations

### 1. Save Answers on Change

```javascript
function saveAnswerToCache(indikatorId, questionId, value) {
  const cache = loadCacheData();
  
  cache.answers[indikatorId][questionId] = {
    value: value,
    timestamp: new Date().toISOString(),
    answered: (value !== null && value !== '')
  };
  
  cache.lastUpdated = new Date().toISOString();
  
  // Save back to localStorage
  saveCacheData(cache);
  
  // Update UI indicators
  updateProgressBar();
  updateQuestionStatus(questionId);
}
```

### 2. Load Aspek Data

```javascript
function loadAspekFromCache(aspekId) {
  const cache = loadCacheData();
  
  // Get all indikators for this aspek
  const indikators = getIndikatorsByAspek(aspekId);
  
  indikators.forEach(indikator => {
    // Get questions for this indikator
    const questions = getQuestionsByIndikator(indikator.id);
    
    questions.forEach(question => {
      // Load cached answer
      const cachedAnswer = cache.answers[indikator.id]?.[question.id];
      
      if (cachedAnswer) {
        // Populate input field dengan cached value
        populateInputField(question.id, cachedAnswer.value);
        updateQuestionStatus(question.id, cachedAnswer.answered);
      }
    });
  });
}
```

### 3. Clear Cache (Reset)

```javascript
function clearCacheData() {
  const periodeId = getPeriodeId();
  const userId = getCurrentUserId();
  const key = `f01_penilaian_${periodeId}_${userId}`;
  
  localStorage.removeItem(key);
  
  // Reload page to reflect changes
  location.reload();
}
```

---

## ✅ Validation Logic & Error Display

### Error Display: Inline Messages

When validation fails, show error message **directly under the unanswered question**:

```html
<div class="pertanyaan-item">
  <label>Apakah dokumen tersedia?</label>
  <div class="input-wrapper">
    <input type="radio" name="q1" />
  </div>
  <!-- Error message inline -->
  <div class="error-message" style="color: #d32f2f; margin-top: 5px;">
    <i class="fas fa-exclamation-circle"></i>
    Pertanyaan wajib diisi
  </div>
</div>
```

**Features**:
- ✓ Red text with icon
- ✓ Small, non-intrusive size
- ✓ Appears below input
- ✓ Can be multiple errors per question
- ✓ Clears when user answers
- ✓ Only shown after validation attempt

### Per-Aspek Validation

```javascript
function validateAspekAnswers(aspekId) {
  const cache = loadCacheData();
  const indikators = getIndikatorsByAspek(aspekId);
  
  const errors = {};
  let hasErrors = false;
  
  indikators.forEach(indikator => {
    const questions = getQuestionsByIndikator(indikator.id);
    
    questions.forEach(question => {
      const answer = cache.answers[indikator.id]?.[question.id];
      
      // Check 1: Skip logic
      if (answer?.skipped) {
        return; // Skip validation for this question
      }
      
      // Check 2: Required fields
      if (question.wajib && (!answer?.value || answer.value === '')) {
        errors[question.id] = `${question.label} wajib diisi`;
        hasErrors = true;
      }
      
      // Check 3: Conditional questions
      if (question.parent_pertanyaan_id) {
        const parentAnswer = cache.answers[indikator.id]?.[question.parent_pertanyaan_id];
        
        // Check if should be visible based on show_when
        const shouldShow = shouldShowConditionalQuestion(
          question.show_when, 
          parentAnswer?.value
        );
        
        if (shouldShow && question.wajib && !answer?.value) {
          errors[question.id] = `${question.label} wajib diisi`;
          hasErrors = true;
        }
      }
    });
  });
  
  return {
    valid: !hasErrors,
    errors: errors
  };
}
```

### Conditional Question Visibility

```javascript
function shouldShowConditionalQuestion(showWhen, parentAnswerValue) {
  if (!parentAnswerValue) return false;
  
  if (showWhen === 'keduanya') {
    return true;
  }
  
  return strtolower(parentAnswerValue) === strtolower(showWhen);
}
```

### Sequential Skip Logic

```javascript
function checkAndApplySkipConditions(indikatorId, questionId, answerValue) {
  const cache = loadCacheData();
  const question = getQuestion(questionId);
  
  // Check if this question has skip_if_answer trigger
  if (question.skip_if_answer) {
    if (strtolower(answerValue) === strtolower(question.skip_if_answer)) {
      // This answer triggers skip
      // Mark all following questions in same indikator as skipped
      
      const allQuestions = getQuestionsByIndikator(indikatorId);
      const questionIndex = allQuestions.findIndex(q => q.id === questionId);
      
      // Mark all questions after this as skipped
      for (let i = questionIndex + 1; i < allQuestions.length; i++) {
        cache.answers[indikatorId][allQuestions[i].id].skipped = true;
        // Hide them in UI
        hideQuestion(allQuestions[i].id, 'Pertanyaan di-skip karena jawaban sebelumnya');
      }
    } else {
      // Un-skip if previously skipped
      // (user changed answer)
    }
  }
  
  saveCacheData(cache);
}
```

---

## 📋 Summary Page

### Layout: Per-Aspek Collapsed View + Edit Links

```
┌───────────────────────────────────────────────────┐
│ Ringkasan Penilaian - Siap Submit                 │
└───────────────────────────────────────────────────┘

┌─ Aspek 1: Manajemen SDM                  10/15 ✓  ┐
│ └─ Indikator 1.1: Kompetensi SDM        5/7      │
│    ├─ Q1: Apakah dokumen tersedia?      Jawab: Ya [Edit ↑]
│    ├─ Q2: Jenis dokumen?               Jawab: Surat Resmi [Edit ↑]
│    └─ Q2-1: Detail dokumen?            Jawab: (conditional) [Edit ↑]
│                                                   
│ └─ Indikator 1.2: Pelatihan SDM        5/8      │
│    ├─ Q3: Sudah dilatih?               Jawab: Ya [Edit ↑]
│    ├─ Q4: Topik pelatihan?             Jawab: [jawaban] [Edit ↑]
│    └─ Q5: Sertifikat?                  Jawab: Tidak - SKIP
│                                                   
└─────────────────────────────────────────────────┘

┌─ Aspek 2: Operasional                    2/20 ✗  ┐
│ └─ Indikator 2.1: Prosedur                0/5    │
│    ├─ Q6: Prosedur ada?                Jawab: (kosong) ⚠ [Edit ↑]
│    ├─ Q7: Tersosialisasi?              Jawab: (kosong) ⚠ [Edit ↑]
│    ...                                           
└─────────────────────────────────────────────────┘

[Back to Form] [Cancel] [Submit Penilaian]
```

### Edit Feature: Quick Navigation

When user clicks **[Edit ↑]** button next to any question:
1. Go back to form view
2. Switch to appropriate Tab (Aspek)
3. **Auto-scroll** to the question
4. Focus/highlight the input field
5. Highlight error message (if any)

```javascript
function editQuestion(questionId, indikatorId) {
  // Get aspek for this indikator
  const indikator = getIndikator(indikatorId);
  const aspekId = indikator.aspek_id;
  
  // 1. Switch to aspek tab
  switchToAspekTab(aspekId);
  
  // 2. Expand the indikator accordion
  expandIndikator(indikatorId);
  
  // 3. Auto-scroll to question with smooth behavior
  const questionElement = document.getElementById(`question_${questionId}`);
  questionElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
  
  // 4. Focus input and highlight
  const input = questionElement.querySelector('input, textarea, select');
  input?.focus();
  input?.classList.add('highlight');
  
  // 5. Show error message (if exists)
  const errorElement = questionElement.querySelector('.error-message');
  if (errorElement) {
    errorElement.classList.add('pulse-animation');
  }
}
```

### Summary Generation

```javascript
function generateSummary() {
  const cache = loadCacheData();
  const aspeks = getAspeks();
  
  const summary = aspeks.map(aspek => {
    const indikators = getIndikatorsByAspek(aspek.id);
    
    const indikatorSummary = indikators.map(indikator => {
      const questions = getQuestionsByIndikator(indikator.id);
      
      const questionSummary = questions.map(question => {
        const answer = cache.answers[indikator.id][question.id];
        
        return {
          id: question.id,
          label: question.label,
          value: answer?.value || '(tidak dijawab)',
          skipped: answer?.skipped,
          required: question.wajib
        };
      });
      
      return {
        id: indikator.id,
        nama: indikator.nama,
        questions: questionSummary
      };
    });
    
    return {
      id: aspek.id,
      nama: aspek.nama,
      indikators: indikatorSummary
    };
  });
  
  return summary;
}
```

---

## 🔐 Admin View (Read-Only Mode)

### Read-Only Page Structure

```
GET /admin/penilaian/{penilaianId}

Same layout as responder, but:
- All inputs disabled (readonly)
- No Next/Previous buttons
- Show metadata:
  ├─ Created by: [User Name]
  ├─ Created at: [Date]
  ├─ Last updated: [Date]
  └─ Status: Draft | Submitted | Approved | Rejected

Action buttons:
[Approve] [Reject] [Request Revision] [Print] [Download PDF]
```

### Data Setup untuk Admin View

```
1. Admin klik penilaian responden
2. System fetch dari database:
   ├─ Get responses table
   ├─ Get all answers per question
   ├─ Structure untuk display same as responder view
3. Populate UI dalam read-only mode
4. Show validation status & error messages if any
```

---

## 🚀 Implementation Steps

### Phase 1: UI Structure (HTML/CSS)
1. Create Tab component for Aspek
2. Create Accordion component for Indikator
3. Create Form inputs styling
4. Create validation message styling
5. Create summary page layout

### Phase 2: Core JavaScript
1. Cache management functions (load/save/clear)
2. Navigation logic (next/prev/tabs)
3. Validation engine
4. Skip condition logic
5. Conditional questions display

### Phase 3: Integration
1. Connect to backend API for data
2. Fetch questions + indikators + aspeks
3. Initialize cache on page load
4. Handle submit to DB

### Phase 4: Admin Features
1. Read-only view mode
2. Response fetching
3. View/Edit/Approve workflow

---

## 📊 Technical Stack

### Frontend
- HTML5 structure
- CSS Grid/Flexbox for layout
- Vanilla JavaScript (or jQuery if needed)
- localStorage API untuk cache
- AJAX (fetch API) untuk submit

### Backend (Existing)
- Laravel for API endpoints
- Database for storing final responses
- New table: `f01_responses` untuk jawaban

### Data Flow
```
Responden fills form
    ↓ [Input change event]
    ↓ updateCache() + updateUI()
    ↓
Responden click [Submit]
    ↓ [Full validation]
    ↓ POST /api/f01/submit
    ↓ Save to database
    ↓ Clear localStorage
    ↓ Redirect to success/summary
    ↓
Admin access /admin/penilaian/{id}
    ↓ Fetch responses
    ↓ Display read-only view
```

---

## 🎯 Key Features Checklist

- [ ] Tab-based Aspek navigation
- [ ] Accordion-based Indikator navigation
- [ ] Auto-save to localStorage
- [ ] Per-Aspek validation
- [ ] Conditional questions logic
- [ ] Sequential skip logic
- [ ] Summary review page
- [ ] Admin read-only view
- [ ] Error handling & messaging
- [ ] Progress tracking
- [ ] Mobile responsive design

---

Status: **READY FOR IMPLEMENTATION**

Next steps:
1. Approve this architecture
2. Start Phase 1: UI Structure
3. Iterate and test each phase
