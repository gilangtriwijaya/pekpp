# F01 Penilaian System - Developer Quick Reference

## System Architecture Quick View

```
Browser Cache                Application Logic             API Backend
┌──────────────────┐  ┌──────────────────┐  ┌──────────────────┐
│ localStorage     │  │ F01Form          │  │F01PenilaianCtrl  │
│                  │  │- Render UI       │  │- Load structure  │
│ Answers:         │  │- Handle input    │  │- Save answers    │
│ {questionId}:val │  │- Show/hide       │  │- Validate        │
│                  │  │                  │  │- Return JSON     │
└──────────────────┘  └──────────────────┘  └──────────────────┘
        ↓                       ↓                      ↓
    F01CacheManager      F01ValidationEngine    f01_pengisian
    - Save/Load          - Conditional logic    f01_jawaban
    - State restore      - Skip logic
    - Metadata           - Form validation
        ↓                       ↓                      ↓
   Session scope         Real-time checks         Persisted
   7-day expiry         On every change            responses
```

## Core Components

### 1. F01Form (in show.blade.php)

**Initialization**
```javascript
// Auto-runs on page load
document.addEventListener('DOMContentLoaded', function() {
  const container = document.getElementById('f01-form-container');
  const pengisianId = container.dataset.pengisianId;
  const status = container.dataset.status;
  F01Form.init(pengisianId, status);
});
```

**Main Methods**
```javascript
F01Form.init(pengisianId, status)        // Start form
F01Form.loadFormData()                   // Fetch structure
F01Form.renderTabs()                     // Create tabs
F01Form.switchTab(tabBtn)                // Change aspek
F01Form.renderAspekContent(aspek)        // Render indikators
F01Form.renderQuestion(q, indikator)     // Create question HTML
F01Form.handleQuestionChange(e)          // Track changes
F01Form.updateProgress()                 // Update progress bar
F01Form.submitForm()                     // Send to server
F01Form.showSummary()                    // Display review modal
```

**Data Structure**
```javascript
F01Form.aspeks = [
  {
    id: 1,
    nama: "Aspek 1",
    progress: 50,
    indikators: [
      {
        id: 10,
        nama: "Indikator 1", 
        questions: [
          {
            id: 100,
            label: "Question text",
            tipe_input: "text|number|textarea|...",
            nilai: "cached answer",
            conditional_questions: [...]
          }
        ]
      }
    ]
  }
];

F01Form.answers = {
  100: "answer value",
  101: "yes",
  102: "checkbox1,checkbox2"
};
```

### 2. F01CacheManager

**Include in View**
```html
<script src="/js/f01-cache-manager.js"></script>
<script>
  const cache = new F01CacheManager(pengisianId, userId);
</script>
```

**Save Answers**
```javascript
// Save to localStorage
cache.saveAnswers(F01Form.answers);

// Later, restore:
const savedAnswers = cache.loadAnswers();
```

**Restore UI State**
```javascript
// Remember which tab was open
cache.saveActiveTab(aspekId);
const lastTab = cache.getActiveTab();

// Remember accordion state
cache.saveExpandedIndikators(['ind-1', 'ind-2']);
const expanded = cache.getExpandedIndikators();

// Remember scroll position
cache.saveScrollPosition('tabContent');
cache.restoreScrollPosition('tabContent');
```

**Validation Caching**
```javascript
// Save per-aspek validation
cache.saveValidationState(aspekId, false, {
  100: "Question required",
  101: "Invalid number"
});

// Get validation state
const state = cache.getValidationState(aspekId);
// { isValid: false, errors: {...}, checkedAt: "..." }
```

### 3. F01ValidationEngine

**Include in View**
```html
<script src="/js/f01-validation-engine.js"></script>
<script>
  const validation = new F01ValidationEngine(aspeks);
</script>
```

**Validate Single Question**
```javascript
const result = validation.validateQuestion(questionId, value);
// { valid: true/false, errors: ["error message"] }
```

**Check Conditional Visibility**
```javascript
// Which children to show for a parent question?
const visible = validation.getVisibleConditionalQuestions(
  parentQuestionId, 
  answers
);
// Returns array of conditional questions that should show
```

**Check if Question Should be Skipped**
```javascript
const shouldSkip = validation.shouldSkipQuestion(questionId, answers);
// true if previous answer triggered skip
// false otherwise
```

**Validate Per-Aspek**
```javascript
const aspekResult = validation.validateAspek(aspekId, answers);
// { 
//   valid: true/false,
//   errors: { questionId: "error message" },
//   skipped: 5 (questions not shown due to skip)
// }
```

**Validate Entire Form**
```javascript
const formResult = validation.validateAll(answers);
// {
//   valid: true/false,
//   errors: { questionId: "error message", ... },
//   byAspek: {
//     aspekId: { valid, errors, skipped },
//     ...
//   }
// }
```

**Display Errors on UI**
```javascript
validation.displayValidationErrors(errors);
// error-{questionId} elements show messages
// Inputs add 'error' class

// Scroll to first error
validation.scrollToFirstError(errors);
```

**Get Summary for Review**
```javascript
const summary = validation.getSummary(answers);
// Array of aspeks with questions and values
```

**Get Completion Stats**
```javascript
const stats = validation.getStatistics(answers);
// {
//   total: 50,
//   answered: 35,
//   required: 40,
//   requiredAnswered: 35,
//   completionPercent: 70,
//   requiredCompletionPercent: 87
// }
```

### 4. F01SpecialFeatures

**Include in View**
```html
<script src="/js/f01-special-features.js"></script>
<script>
  const features = new F01SpecialFeatures(F01Form);
</script>
```

**Generate Summary Modal**
```javascript
// Generate HTML for summary review
const summaryHtml = features.generateSummary(answers);

// Show in modal
document.getElementById('summaryContent').innerHTML = summaryHtml;

// Attach event listeners (Edit buttons)
features.attachSummaryEventListeners();
```

**Navigate to Question from Summary**
```javascript
// User clicks "Edit" button in summary
features.scrollToQuestion(questionId, aspekId);
// Will:
// 1. Switch to correct tab
// 2. Open accordion
// 3. Scroll into view
// 4. Focus input
// 5. Highlight briefly
```

**Quick Navigation Sidebar**
```javascript
features.generateQuickNav();  // Attached to DOM
features.toggleQuickNav();    // Show/hide
// Fixed-position right sidebar with aspek→indikator nav
```

**Scroll to Top Button**
```javascript
features.addScrollToTopButton();
// Floating button appears after 300px scroll
// Sticky to bottom-right corner
```

**Print Support**
```javascript
features.printSummary(answers);
// Opens print-friendly view in new window
```

### 5. F01AdminReview

**Include in Admin View**
```html
<div id="f01-admin-review" data-pengisian-id="123"></div>
<script src="/js/f01-admin-review.js"></script>
<script>
  const admin = new F01AdminReview(pengisianId);
  admin.load();  // Fetch and render
</script>
```

**Admin Display**
```javascript
// Read-only interface with:
// - Header with metadata
// - Tab navigation (like user view)
// - Accordion with answers
// - Action buttons:
//   - Approve
//   - Reject
//   - Print
//   - Export PDF
```

**Answer Formatting for Display**
```javascript
// Internal method that handles:
// - Textarea: "\n" → "<br>"
// - Number: locale formatting
// - Checkbox: "opt1,opt2" → "opt1, opt2"
// - Truncate: > 100 chars
```

---

## API Endpoints

### 1. Create/Get Form

**Endpoint**: `POST /api/f01/pengisian`

**Request**
```json
{
  "periode_id": 1,
  "upp_id": 5
}
```

**Response** (200 OK)
```json
{
  "success": true,
  "data": {
    "pengisian_id": 123,
    "periode": { "id": 1, "nama": "Q1-2026" },
    "upp": { "id": 5, "nama": "UPP A" },
    "status": "draft",
    "aspeks": [/* structure */]
  }
}
```

### 2. Bulk Submit Answers

**Endpoint**: `POST /api/f01/submit`

**Request**
```json
{
  "pengisian_id": 123,
  "answers": [
    { "pertanyaan_id": 100, "nilai": "text answer" },
    { "pertanyaan_id": 101, "nilai": "Ya" },
    { "pertanyaan_id": 102, "nilai": "opt1,opt2" }
  ]
}
```

**Response** (200 OK)
```json
{
  "success": true,
  "message": "Penilaian berhasil disubmit",
  "pengisian_id": 123,
  "status": "submitted"
}
```

**Response** (422 Validation Error)
```json
{
  "success": false,
  "message": "Validasi gagal...",
  "errors": {
    "100": "Pertanyaan wajib diisi"
  }
}
```

### 3. Per-Aspek Validation

**Endpoint**: `POST /api/f01/validate`

**Request**
```json
{
  "aspek_id": 1,
  "answers": {
    "100": "answer",
    "101": ""
  }
}
```

**Response** (200 OK)
```json
{
  "valid": false,
  "errors": {
    "101": "Pertanyaan wajib diisi"
  }
}
```

### 4. Get Pengisian for Admin

**Endpoint**: `GET /api/f01/{pengisianId}`

**Response** (200 OK)
```json
{
  "success": true,
  "data": {
    "pengisian_id": 123,
    "periode": { /* ... */ },
    "upp": { /* ... */ },
    "status": "submitted",
    "aspeks": [/* full structure with answers */]
  }
}
```

---

## Integration Example

### Complete User Flow

```javascript
// 1. Page loads
document.addEventListener('DOMContentLoaded', () => {
  // Initialize core system
  const cache = new F01CacheManager(pengisianId);
  const validation = new F01ValidationEngine([]); // Will be updated
  const features = new F01SpecialFeatures(F01Form);
  
  // Initialize form
  F01Form.init(pengisianId, status);
  
  // Try to restore state
  if (cache.hasCacheData()) {
    const saved = cache.loadAnswers();
    Object.assign(F01Form.answers, saved);
  }
  
  // Restore UI position
  const lastTab = cache.getActiveTab();
  if (lastTab) {
    const tabBtn = document.querySelector(`[data-aspek-id="${lastTab}"]`);
    tabBtn?.click();
  }
  
  const expanded = cache.getExpandedIndikators();
  expanded.forEach(id => {
    const header = document.querySelector(`[data-indikator-id="${id}"]`);
    if (header && !header.classList.contains('expanded')) {
      header.click();
    }
  });
  
  cache.restoreScrollPosition('tabContent');
});

// 2. User changes answer
function handleChange(e) {
  F01Form.handleQuestionChange(e);
  
  // Auto-save to cache
  cache.saveAnswers(F01Form.answers);
  
  // Re-validate
  validation = new F01ValidationEngine(F01Form.aspeks);
  const result = validation.validateAspek(currentAspekId, F01Form.answers);
  cache.saveValidationState(currentAspekId, result.valid, result.errors);
}

// 3. User views summary
function showSummary() {
  const summaryHtml = features.generateSummary(F01Form.answers);
  document.getElementById('summaryContent').innerHTML = summaryHtml;
  features.attachSummaryEventListeners();
  document.getElementById('summaryModal').style.display = 'flex';
}

// 4. User submits
async function submitPenilaian() {
  const validation = new F01ValidationEngine(F01Form.aspeks);
  const result = validation.validateAll(F01Form.answers);
  
  if (!result.valid) {
    validation.displayValidationErrors(result.errors);
    validation.scrollToFirstError(result.errors);
    alert('Ada pertanyaan yang belum diisi');
    return;
  }
  
  const answers = Object.entries(F01Form.answers).map(([qId, val]) => ({
    pertanyaan_id: parseInt(qId),
    nilai: val
  }));
  
  const response = await fetch('/api/f01/submit', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      pengisian_id: F01Form.pengisianId,
      answers: answers
    })
  });
  
  if (response.ok) {
    cache.clearCache();
    alert('Penilaian berhasil disubmit');
    window.location.reload();
  }
}
```

---

## Common Patterns

### Listen for Question Changes

```javascript
document.addEventListener('change', (e) => {
  if (e.target.dataset.questionId) {
    const qId = e.target.dataset.questionId;
    const value = e.target.value;
    
    F01Form.answers[qId] = value;
    F01Form.updateProgress();
    cache.saveAnswers(F01Form.answers);
  }
});
```

### Get All Unanswered Required Questions

```javascript
const unanswered = [];
for (let q of validation.questions.values()) {
  if (q.wajib && !F01Form.answers[q.id]) {
    unanswered.push(q);
  }
}
```

### Check Skip Status for All Questions

```javascript
const skipped = [];
for (let q of validation.questions.values()) {
  if (validation.shouldSkipQuestion(q.id, F01Form.answers)) {
    skipped.push(q);
  }
}
```

### Update Progress Bar Manually

```javascript
const total = validation.questions.size;
const answered = Object.keys(F01Form.answers)
  .filter(qId => F01Form.answers[qId]).length;
const percent = Math.round((answered / total) * 100);

document.getElementById('globalProgress').style.width = percent + '%';
document.getElementById('progressText').text = `${answered}/${total} pertanyaan`;
```

---

## Debugging Tips

### Check Cache State
```javascript
const cache = new F01CacheManager(pengisianId);
console.log(cache.exportCache());
// View all cached data and metadata
```

### View Question Map
```javascript
const validation = new F01ValidationEngine(F01Form.aspeks);
console.log(validation.questions);
// See all questions flattened with metadata
```

### Check Form State
```javascript
console.log(F01Form.answers);
// See all current answers
```

### Simulate Skip Condition
```javascript
// Manually check if question would be skipped
console.log(validation.shouldSkipQuestion(questionId, F01Form.answers));
```

### View Conditional Visibility
```javascript
// See which children should show for parent
const visible = validation.getVisibleConditionalQuestions(
  parentId, 
  F01Form.answers
);
console.log(visible);
```

### Validate Manually
```javascript
const result = validation.validateAll(F01Form.answers);
console.log(result);
// { valid, errors, byAspek }
```

---

## Performance Checklist

- [ ] Use event delegation (addEventListener on container)
- [ ] Avoid repeated DOM queries (cache selectors)
- [ ] Don't validate on every keystroke (use debounce)
- [ ] Lazy-load conditional questions only when needed
- [ ] Clear expired caches periodically
- [ ] Use Promise for async operations
- [ ] Minimize reflows during rendering

---

## Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | 2026-02-15 | Initial release - All 6 phases complete |

---

## Support & Troubleshooting

**Form won't load:**
- Check browser console for JS errors
- Verify API endpoint returns valid JSON
- Ensure localStorage is available

**Cache not restoring:**
- Check if cache exists: `localStorage.getItem('f01_penilaian_123')`
- Check cache age: `cache.hasCacheData()`
- Clear old cache: `F01CacheManager.clearExpiredCaches()`

**Validation not working:**
- Verify validation engine initialized with aspeks
- Check answer format matches question type
- Test with: `validation.validateQuestion(qId, value)`

**Conditional questions not showing:**
- Check parent question answer is saved
- Verify show_when value (ya/tidak/keduanya)
- Inspect: `validation.getVisibleConditionalQuestions(parentId, answers)`

**Skip logic not triggering:**
- Verify skip_if_answer is set on question
- Check previous answer matches exactly (case-insensitive)
- Test: `validation.shouldSkipQuestion(qId, answers)`

---

**Last Updated**: 2026-02-15
**Status**: Production Ready
**Maintained By**: Development Team
