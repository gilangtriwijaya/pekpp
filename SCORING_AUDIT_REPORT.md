# Scoring Calculation Audit Report

## Executive Summary
Comprehensive audit of all scoring calculation logic across F02, F03, and Final Index (Indeks Pelayanan Publik) modules. Purpose: Identify bugs, inconsistencies, and potential improvements.

**Audit Date:** Current  
**Status:** ✅ Complete  

---

## 1. F02 VALIDATION SCORE CALCULATION

### 1.1 Overview
F02 converts F01 self-assessment data into validated scores (0-100 scale) using aspek-level weighting.

### 1.2 Data Flow
```
F01 Jawaban → F02 Validasi (validator scores each indicator) → F02 IndikatorValidasi → Total Nilai
```

### 1.3 Score Calculation Formula

**Location:** [F02ValidasiController.php](app/Http/Controllers/F02ValidasiController.php#L315-L345)

```
FOR each aspek:
    aspek_bobot = aspek.bobot (%)
    indikator_list = all indikators in this aspek
    nilai_list = get F02IndikatorValidasi.nilai for all indicators in aspek
    
    avg_nilai_aspek = SUM(nilai_list) / COUNT(nilai_list)
    aspek_contribution = (avg_nilai_aspek * aspek_bobot) / 100
    
    total_nilai += aspek_contribution
    
total_nilai = ROUND(total_nilai, 2)
```

#### Code Reference (Lines 315-345):
```php
$totalNilai = 0;
foreach ($aspeks as $aspek) {
    $bobot = $aspek->bobot ?? 0;
    $indikatorIds = $aspek->indikator->pluck('id')->toArray();
    
    $nilaiList = F02IndikatorValidasi::where('f02_validasi_id', $validasi->id)
        ->whereIn('indikator_id', $indikatorIds)
        ->whereNotNull('nilai')
        ->pluck('nilai')
        ->toArray();
    
    if (!empty($nilaiList)) {
        $avgNilaiAspek = array_sum($nilaiList) / count($nilaiList);
        $totalNilai += ($avgNilaiAspek * $bobot) / 100;
    }
}
```

### 1.4 Indicator Score Range
- **Range:** 0 - 5 (typically)
- **Stored in:** `f02_indikator_validasi.nilai`
- **Input by:** Validator (F02 validation form)

### 1.5 Validation Requirements
- ✅ All indicators in all aspects must be scored before finalization (line 300-310)
- ✅ Bobot percentages should sum to 100% (validation at config level, not enforced here)
- ⚠️ Empty nilai_list results in 0 contribution (skips that aspect silently)

---

## 2. F03 SATISFACTION SURVEY SCORE CALCULATION

### 2.1 Overview
F03 captures public satisfaction survey responses with 1-5 Likert scores, stores as simple averages per UPP/token.

### 2.2 Data Flow
```
F03 Public Form Input → F03 Jawaban (score stored) → F03 Pengisian.average_score (accessor)
                                                  → Dashboard aggregation
```

### 2.3 Score Levels
- **Individual Question:** `f03_jawaban.score` (1-5 integer, Likert scale)
- **Per Response:** `F03Pengisian.average_score` (accessor from jawaban average)
- **Per Token/UPP:** Average of all responses

### 2.4 Calculation Methods

#### 2.4.1 Per-Response Average (Model Accessor)
**Location:** [F03Pengisian.php](app/Models/F03Pengisian.php#L57-L60)

```php
public function getAverageScoreAttribute()
{
    return $this->jawaban()->avg('score') ?? 0;
}
```

**What it does:**
- Calculates average score for a single F03 response (pengisian)
- Simple database average across all jawaban for that pengisian

#### 2.4.2 Per-Aspek Average Scores
**Location:** [DashboardController.php](app/Http/Controllers/DashboardController.php#L416)

```php
foreach ($aspeks as $aspek) {
    $indikatorIds = $aspek->indikator()->pluck('id')->toArray();
    
    $avgScore = F03Jawaban::whereIn('f03_indikator_id', $indikatorIds)
        ->whereHas('pengisian', function($q) use ($periodeId, $selectedUppIds) {
            $q->where('periode_id', $periodeId)
                ->whereIn('upp_id', $selectedUppIds);
        })
        ->avg('score') ?? 0;
    
    $f03AspekScores[$aspek->nama] = round($avgScore, 2);
}
```

**What it does:**
- Groups responses by F03 aspek
- Calculates average score for all questions in that aspek
- Returns per-aspek breakdowns

#### 2.4.3 Global F03 Average
**Location:** [DashboardController.php](app/Http/Controllers/DashboardController.php#L425)

```php
$averageScore = F03Jawaban::whereHas('pengisian', function($q) use ($periodeId, $selectedUppIds) {
    $q->where('periode_id', $periodeId)
        ->whereIn('upp_id', $selectedUppIds);
})
->avg('score') ?? 0;
```

**What it does:**
- Simple average across ALL F03 jawaban for selected filters
- No weighting applied

#### 2.4.4 UPP-Level Scores (Admin Dashboard)
**Location:** [F03DashboardController.php](app/Http/Controllers/F03DashboardController.php#L160-L175)

```php
$tokenJawabanScores = F03Jawaban::whereIn('f03_pengisian_id', $tokenResponses->pluck('id'))->avg('score') ?? 0;
$uppScoreMap[$uppId]['total_score'] += $tokenJawabanScores;

// Calculate averages
foreach ($uppScoreMap as $data) {
    $avgScore = $data['response_count'] > 0 ? $data['total_score'] / $data['response_count'] : 0;
    // ... save to rankings
}
```

**What it does:**
- For each token, gets average score across all responses
- Sums token averages and divides by response_count to get UPP average
- **⚠️ ISSUE IDENTIFIED** (See section 2.5)

### 2.5 🐛 BUGS & INCONSISTENCIES IN F03 SCORING

#### Bug 2.5.1: Incorrect UPP Average Calculation in Admin Dashboard
**Severity:** 🔴 HIGH - Produces wrong UPP rankings

**Location:** [F03DashboardController.php](app/Http/Controllers/F03DashboardController.php#L160-L175)

**Problem:**
```php
$uppScoreMap[$uppId]['total_score'] += $tokenJawabanScores;  // Sum of token averages
$uppScoreMap[$uppId]['response_count']++;                      // Count of TOKENS, not responses

foreach ($uppScoreMap as $data) {
    $avgScore = $data['response_count'] > 0 
        ? $data['total_score'] / $data['response_count']  // ❌ WRONG: dividing by token count
        : 0;
}
```

**Example Scenario:**
- Token 1: 4 responses, avg score = 4.5
- Token 2: 2 responses, avg score = 4.0

**Current (Wrong) Calculation:**
```
total_score = 4.5 + 4.0 = 8.5
response_count = 2 (tokens)
avg = 8.5 / 2 = 4.25 ❌ WRONG
```

**Correct Calculation:**
```
Should be: (4.5*4 + 4.0*2) / (4+2) = (18 + 8) / 6 = 4.33 ✅
```

**Root Cause:**
- `response_count` is incremented once per token (line 165: `$uppScoreMap[$uppId]['response_count']++`)
- Should be incremented by actual response count for that token
- Or should calculate from raw jawaban pool directly

**Fix Options:**

**Option A (Recommended):** Calculate directly from jawaban
```php
$allResponseCount = 0;
foreach ($uppScoreMap as $uppData) {
    $allJawabanAvg = F03Jawaban::whereIn('f03_pengisian_id', 
        F03Pengisian::where('upp_id', $uppData['upp_id'])
                    ->where('periode_id', $periodeId)
                    ->pluck('id')
    )->avg('score') ?? 0;
    
    $uppScoreMap[$uppId]['average_score'] = round($allJawabanAvg, 2);
}
```

**Option B:** Track actual response count
```php
// At line 165, replace:
$uppScoreMap[$uppId]['response_count']++;

// With:
$uppScoreMap[$uppId]['response_count'] += $responseCount;  // Add actual response count
```

---

## 3. FINAL INDEX: INDEKS PELAYANAN PUBLIK

### 3.1 Overview
Final index combines F02 (75% weight) and F03 (25% weight) scores into single public service quality index.

### 3.2 Formula

**Location:** [DashboardController.php](app/Http/Controllers/DashboardController.php#L151-L165)

```
F02 Score Range:     0-100
F03 Score Range:     1-5

// Normalize both to 1-5 scale
F02_normalized = F02_score / 20     (converts 0-100 to 0-5)
F03_normalized = F03_score (already 1-5)

// Weighted formula
Indeks_Nilai = (F02_normalized * 0.75) + (F03_normalized * 0.25)

// Result: 0-5 scale
```

### 3.3 Code Implementation

```php
// Line 159-165
$f02Value = $f02 ? ($f02['total_nilai'] ?? 0) : 0;
$f02Normalized = $f02Value / 20;  // Convert 0-100 to 0-5
$finalIndex = ($f02Normalized * 0.75) + ($f03Average * 0.25);

// Line 185
'indeks_nilai' => round($finalIndex, 2),
```

### 3.4 Score Interpretation
Based on code comments and implementation:

```
Indeks Value        Category
≥ 3.01             Baik (B-) / Good
< 3.01             Perlu Pembinaan (Needs Improvement)
```

**Location:** [DashboardController.php](app/Http/Controllers/DashboardController.php#L214-L225)

```php
$upp_baik_count = $collection->filter(function($item) {
    return $item['indeks_nilai'] >= 3.01;
})->count();
```

### 3.5 Example Calculations

**Scenario 1: Medium Performance**
```
F02 = 60 (out of 100)
F03 = 4.0 (average satisfaction)

F02_norm = 60 / 20 = 3.0
Indeks = (3.0 * 0.75) + (4.0 * 0.25)
       = 2.25 + 1.0
       = 3.25 ✅ (Good/Baik)
```

**Scenario 2: Low F03, Good F02**
```
F02 = 80
F03 = 1.5

F02_norm = 80 / 20 = 4.0
Indeks = (4.0 * 0.75) + (1.5 * 0.25)
       = 3.0 + 0.375
       = 3.375 ✅ (Good)
```

**Scenario 3: High F03 Only**
```
F02 = 0 (not validated yet)
F03 = 5.0

F02_norm = 0 / 20 = 0
Indeks = (0 * 0.75) + (5.0 * 0.25)
       = 0 + 1.25
       = 1.25 ❌ (Low, needs improvement despite perfect satisfaction)
```

### 3.6 🐛 ISSUES IN FINAL INDEX CALCULATION

#### Issue 3.6.1: F03 Not Required Before F02
**Severity:** 🟡 MEDIUM - Incomplete scoring logic

**Location:** [DashboardController.php](app/Http/Controllers/DashboardController.php#L151-P165)

**Problem:**
The formula applies F03 as part of final index calculation even if:
- No F03 data exists (F03 responses = 0)
- F03 survey hasn't been conducted yet
- F03 may not be applicable to this periode/UPP

**Current Logic:**
```php
$f03Pengisan = $f03Data->where('upp_id', $uppId);
$f03Average = $f03Pengisan->count() > 0
    ? $f03Pengisan->avg('average_score')
    : 0;  // ❌ Defaults to 0 if no responses

// No check if F03 should be included
$finalIndex = ($f02Normalized * 0.75) + ($f03Average * 0.25);
```

**Result:**
- If F03 not available, index = F02_norm * 0.75 (max 3.75 on 0-5 scale)
- UPPs will always show lower scores even with perfect F02, if F03 missing
- Scenario: F02=100 → index=3.75 (Baik) instead of 5.0

**Expected Behavior** (per documentation):
From [F03_IMPLEMENTATION_COMPLETE.md](F03_IMPLEMENTATION_COMPLETE.md#L152):
```
Final Score Formula: F02 (75%) + F03 (25%)
  - **IF** target responses met
  - **ELSE** F02 only (F03 excluded)
```

**But Code Shows:** ❌ No check for target responses! Always weights both.

#### Fix:
```php
// Check if F03 target met
$periode = $validasi->periode ?? Periode::find($periodeId);
$f03TargetMet = $f03Pengisan->count() >= ($periode->target_responden_f03 ?? 0);

if ($f03TargetMet && $f03Pengisan->count() > 0) {
    // Use both: 75% F02 + 25% F03
    $finalIndex = ($f02Normalized * 0.75) + ($f03Average * 0.25);
} else {
    // Use F02 only: Scale 0-100 to 0-5 gives max 5.0
    $finalIndex = $f02Normalized;  // Or normalize differently
}
```

#### Issue 3.6.2: No Null Safety on F02 Value
**Severity:** 🟡 MEDIUM - Graceful fallback but unclear intent

**Location:** [DashboardController.php](app/Http/Controllers/DashboardController.php#L151)

**Code:**
```php
$f02Value = $f02 ? ($f02['total_nilai'] ?? 0) : 0;
```

**Problem:**
- If F02 not validated yet, defaults to 0
- Index calculation proceeds with 0, giving very low scores
- User sees 0.625 index instead of "N/A" or "In Progress"

**Current Behavior:**
```
F02 not validated → Indeks = 0.625 (still shows number, misleads)
```

**Better Approach:**
```php
if (!$f02 || $f02['total_nilai'] === null) {
    $aggregatedData[] = [
        // ... other fields
        'f02_nilai' => null,
        'indeks_nilai' => null,  // Show explicitly null/unavailable
        'status' => 'pending_validation',  // Add status field
    ];
    continue;  // Skip calculating index
}
```

---

## 4. ASPEK WEIGHTING SYSTEM

### 4.1 F02 Aspek Bobot

**Purpose:** Weight the contribution of each aspek to final F02 score

**Formula:**
```
total_nilai = Σ(avg_nilai_aspek[i] * bobot[i] / 100) for all aspeks
```

**Bobot:** Stored in `aspek.bobot` (integer percentage)

**Validation:** ❌ NOT ENFORCED in code
- Code assumes bobot values sum to 100%
- No database check exists
- If sum ≠ 100%, final score will be wrong

**Example With Wrong Bobot Sum:**
```
Aspek A: bobot=30, nilai=80 → contribution = 80*30/100 = 24.0
Aspek B: bobot=50, nilai=90 → contribution = 90*50/100 = 45.0
Aspek C: bobot=10, nilai=70 → contribution = 70*10/100 = 7.0
                    (SHOULD BE 60%)

Total = 24 + 45 + 7 = 76 ❌ (Only 90% of possible, should be max 100)
```

### 4.2 F03 Aspek Bobot

**Status:** ❌ NOT USED
- `f03_aspek.bobot` field exists
- But dashboard calculations **ignore it completely**
- All aspeks treated with equal weight

**Code:** [DashboardController.php](app/Http/Controllers/DashboardController.php#L416-L427)
```php
foreach ($aspeks as $aspek) {
    // No bobot applied here
    $avgScore = F03Jawaban::whereIn('f03_indikator_id', $indikatorIds)
        ->avg('score');  // Simple average, no weighting
}
```

**Impact:**
- F03 aspeks with high importance score same as low importance
- If aspek E is critical but has few responses, diluted with other aspeks
- Score not reflecting intended importance hierarchy

---

## 5. KEY OBSERVATIONS & RECOMMENDATIONS

### 5.1 Summary of Issues

| # | Issue | Severity | Location | Type |
|---|-------|----------|----------|------|
| 1 | F03 UPP average calculation wrong (token count vs response count) | 🔴 HIGH | F03DashboardController.php:160-175 | Bug |
| 2 | F03 weighting not applied despite bobot field existing | 🟡 MEDIUM | DashboardController.php:416 | Design Issue |
| 3 | F02 aspek bobot not validated to sum to 100% | 🟡 MEDIUM | F02ValidasiController.php | Validation Gap |
| 4 | Final index ignores F03 target response threshold | 🟡 MEDIUM | DashboardController.php:159-165 | Logic Error |
| 5 | F02 null not explicitly handled, defaults to 0 | 🟡 MEDIUM | DashboardController.php:151 | UX Issue |
| 6 | No validation that all indicators scored before F02 finalize | ⚠️ MINOR | F02ValidasiController.php:300-310 | Working as designed |

### 5.2 Recommendations

#### Priority 1 (Critical): Fix F03 UPP Ranking Formula
**Impact:** Affects admin dashboard rankings and UPP comparisons

**Action:**
- Modify [F03DashboardController.php](app/Http/Controllers/F03DashboardController.php) lines 160-175
- Calculate UPP averages from raw jawaban pool, not token averages
- Test with multiple tokens per UPP to verify correctness

#### Priority 2 (Important): Implement F03 Target Response Check
**Impact:** Compliance with documented requirements about F02 (75%) + F03 (25%) weighting

**Action:**
- Add target response threshold check in [DashboardController.php](app/Http/Controllers/DashboardController.php#L159)
- If F03 target not met, use F02-only formula
- Document expected behavior in code comments

#### Priority 3 (Important): Apply F03 Aspek Bobot
**Impact:** F03 scores not reflecting intended importance of each aspek

**Action:**
- Update [DashboardController.php](app/Http/Controllers/DashboardController.php#L416) calculateF03AspekScores()
- Apply bobot weighting like F02 does
- Validate F03 bobot values sum to 100%

#### Priority 4 (Nice-to-Have): Validate F02 Aspek Bobot Sum
**Impact:** Prevents accidental misconfiguration of scores

**Action:**
- Add database constraint or validation in Aspek model
- Alert admin if bobot for a periode doesn't sum to 100%
- Log warning when calculating with invalid bobot sum

#### Priority 5 (Polish): Handle Null/Missing F02 Data
**Impact:** Clearer reporting and avoid misleading low scores

**Action:**
- Modify getDashboardData() to skip null F02 calculations
- Show "Pending Validation" status instead of low score
- Improve UX of dashboard for in-progress assessments

---

## 6. TESTING RECOMMENDATIONS

### 6.1 Test Cases for Fixes

#### Test F03 UPP Ranking Fix
```
Setup:
  - Token 1 (UPP A): 3 responses (scores 3, 4, 5)
  - Token 2 (UPP A): 2 responses (scores 4, 4)

Current (Wrong): (4.0 + 4.0) / 2 = 4.0
Fixed (Correct): (3+4+5+4+4) / 5 = 4.0

Setup 2:
  - Token 1: 1 response (score 5)
  - Token 2: 9 responses (scores 1,1,1,1,1,1,1,1,1)

Current: (5 + 1) / 2 = 3.0 ❌ WRONG
Fixed:   (5 + 1+1+1+1+1+1+1+1+1) / 10 = 1.4 ✅ CORRECT
```

#### Test F03 Target Threshold
```
Setup:
  - F02 = 80 (score: 4.0)
  - F03 responses = 3, target = 10
  - F03 average = 5.0

Without fix: Indeks = (4.0 * 0.75) + (5.0 * 0.25) = 4.25
With fix:    Indeks = 4.0 (F02 only, since F03 target not met)
```

#### Test F03 Aspek Bobot Application
```
Setup:
  - Aspek 1 (bobot=70): Avg score = 4.0
  - Aspek 2 (bobot=30): Avg score = 2.0

Current: (4.0 + 2.0) / 2 = 3.0 ❌ WRONG (equal weight)
Fixed:   (4.0*0.7) + (2.0*0.3) = 2.8 + 0.6 = 3.4 ✅ CORRECT (weighted)
```

---

## 7. IMPLEMENTATION NOTES

### 7.1 Files to Modify

1. **[app/Http/Controllers/F03DashboardController.php](app/Http/Controllers/F03DashboardController.php)**
   - Lines 165-175: Fix UPP average calculation

2. **[app/Http/Controllers/DashboardController.php](app/Http/Controllers/DashboardController.php)**
   - Lines 159-165: Add F03 target threshold check
   - Lines 416-427: Apply F03 aspek bobot weighting
   - Lines 151-156: Add null/missing F02 handling

3. **[app/Models/Aspek.php](app/Models/Aspek.php)** (Optional)
   - Add accessor/method to validate bobot sum

### 7.2 Backward Compatibility
- Changes to F03 dashboard calculation may alter existing UPP rankings
- Previous ranking data should be recalculated from raw jawaban
- Admin should be notified of ranking changes after fix

### 7.3 Database Consistency
- No database migrations needed
- All changes are logic/formula-level
- Existing data will be recalculated on next dashboard load

---

## 8. CONCLUSION

**Overall Assessment:** ✅ System architecture sound, ⚠️ but multiple calculation bugs present

**Key Findings:**
1. ✅ F02 scoring formula correct and properly weighted
2. ❌ F03 UPP averages calculated incorrectly (HIGH priority)
3. ⚠️ F03 aspek weights not implemented despite infrastructure
4. ⚠️ Final index ignores documented target response requirement  
5. ⚠️ No validation of aspek bobot percentages

**Estimated Fix Complexity:**
- **Quick wins:** Issues 1, 4, 5 (< 30 min each)
- **Medium:** Issues 2, 3 (1-2 hours each)
- **Total:** ~4-5 hours for complete audit implementation

**Risk Level:** 🟡 MEDIUM
- Fixes will improve accuracy but may change historical rankings
- Should be applied with admin awareness of score changes

---

**Report Generated:** 2024  
**Audit Scope:** F02, F03, Final Index Calculations  
**Status:** Ready for Implementation
