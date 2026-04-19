# Detailed Filter Implementation Analysis: Working vs Non-Working

## Executive Summary

**WORKING**: `/resources/views/dashboard/index.blade.php` - Traditional form-based filtering with page reload
**NON-WORKING**: `/resources/views/livewire/analytics/panel.blade.php` - Livewire-based filtering with event dispatch

**Root Cause**: The analytics panel relies on Livewire event handling that either doesn't exist or doesn't properly update the chart data in `window.chartDataFromServer`.

---

## PART 1: EVENT FLOW COMPARISON

### 1.1 WORKING - Dashboard Filter (Traditional Approach)

#### Button Click Handler
```javascript
// Dashboard: Line ~1070-1090
if (submitModalBtn) {
    submitModalBtn.addEventListener('click', function() {
        const selectedValues = Array.from(uppCheckboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.value);  // ✅ ALL selected values stored as array

        if (selectedValues.length === 0) {
            alert('Pilih minimal satu UPP!');
            return;
        }

        // Update hidden form with ALL selected UPPs
        const uppSelect = document.getElementById('uppSelect');
        Array.from(uppSelect.options).forEach(option => {
            option.selected = selectedValues.includes(option.value);
        });

        // AJAX save preference first
        fetch('{{ route("dashboard.save-preferred-upps") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({
                upp_ids: selectedValues  // ✅ Send ALL selected IDs
            })
        }).then(response => {
            // Submit form to update view
            document.getElementById('filterForm').submit();  // ✅ Page reload
        }).catch(error => {
            console.error('Error saving preference:', error);
            document.getElementById('filterForm').submit();
        });
    });
}
```

#### Data Flow
```
Click Submit Button
  ↓
Collect ALL selected UPP IDs → Array[int]
  ↓
AJAX POST save preference (with ALL ids)
  ↓
Form submit() → Page reload
  ↓
URL: ?upp_ids[]=1&upp_ids[]=2&upp_ids[]=3
  ↓
Server processes filter, returns filtered data
  ↓
Page renders with fresh data
  ↓
JavaScript initializes charts with new data
```

#### How Charts Get Updated
```javascript
// Dashboard: Line ~1210
document.addEventListener('DOMContentLoaded', function() {
    if (chartDataCache.length > 0) {
        initializeCharts();  // Charts init with pre-loaded data
    }
});

// Chart data passed directly as PHP array
const chartDataCache = @json(isset($dashboardData['upps']) ? $dashboardData['upps'] : []);
const summaryData = @json(isset($dashboardData['summary']) ? $dashboardData['summary'] : []);

// After page reload, chartDataCache contains filtered data
// Charts automatically get new data
```

---

### 1.2 NON-WORKING - Analytics Filter (Livewire Approach)

#### Button Click Handler
```javascript
// Analytics: Line ~210-240
submitModalBtn.addEventListener('click', function() {
    console.log('--- MODAL SUBMIT CLICKED ---');
    const selectedValues = Array.from(uppCheckboxes)
        .filter(cb => cb.checked)
        .map(cb => cb.value);

    console.log('✓ Selected checkboxes:', selectedValues.length);
    console.log('  Values:', selectedValues);

    if (selectedValues.length === 0) {
        console.error('❌ No checkboxes selected!');
        alert('Pilih minimal satu UPP!');
        return;
    }

    // ❌ PROBLEM 1: ONLY TAKES FIRST SELECTED UPP
    const uppId = parseInt(selectedValues[0]);  // Takes ONLY first ID!
    console.log('✓ Taking first UPP ID:', uppId);  // Debug shows only 1st taken

    if (window.Livewire) {
        try {
            console.log('🔄 >>> DISPATCHING setUppFilter with upp_id:', uppId);
            // ❌ PROBLEM 2: Dispatches to Livewire component
            Livewire.dispatch('setUppFilter', { upp_id: uppId });

            console.log('✓ >>> Dispatch call completed');
            console.log('⏳ Waiting for PHP event handler and livewire:updated...');
        } catch (error) {
            console.error('❌ Error during dispatch:', error);
        }
    } else {
        console.error('❌ Livewire not found!');
    }

    closeModal();
});
```

#### Data Flow - Expected (But Broken)
```
Click Submit Button
  ↓
Collect selected values (e.g., [1, 2, 3])
  ↓
Take ONLY FIRST ID: 1
  ↓
Livewire.dispatch('setUppFilter', { upp_id: 1 })
  ↓
PHP Component @Listen or #[On('setUppFilter')] ??? (MISSING/BROKEN)
  ↓
If handler exists: Update $upp_id public property
  ↓
Component re-render
  ↓
window.chartDataFromServer updated (???  probably not)
  ↓
livewire:updated event
  ↓
JavaScript tries to re-init charts
  ↓
BUT: window.chartDataFromServer still has OLD data!
  ↓
Charts show same data as before
```

---

## PART 2: DATA LOADING & BINDING

### 2.1 WORKING - Dashboard (Page Reload = Guaranteed Fresh Data)

#### Initial Data Embedding
```blade
{{-- Dashboard: Line ~1215-1217 --}}
<script>
    let comparisonChart = null;
    let indeksChart = null;
    // ... other chart instances
    const chartDataCache = @json(isset($dashboardData['upps']) ? $dashboardData['upps'] : []);
    const summaryData = @json(isset($dashboardData['summary']) ? $dashboardData['summary'] : []);

    document.addEventListener('DOMContentLoaded', function() {
        if (chartDataCache.length > 0) {
            initializeCharts();
        }
        // ... modal setup
    });
</script>
```

#### How Data Gets Refreshed
```
1. User filters and clicks submit
2. Form submits with <form method="GET" id="filterForm">
3. Server processes: route("dashboard.*") with upp_ids[] params
4. PHP returns NEW page with filtered data
5. ENTIRE page reloads
6. NEW chartDataCache = @json($dashboardData['upps']) with filtered data
7. JavaScript reads new chartDataCache
8. Charts render with new data
```

#### No Update Event Needed
- The page reload guarantees fresh data
- JavaScript doesn't need to listen for update events
- Data is embedded directly in HTML on each page load

---

### 2.2 NON-WORKING - Analytics (Depends on Window Object Update)

#### Initial Data Embedding
```blade
{{-- Analytics: Line ~85-95 --}}
<script>
    // Initialize chart data from Blade (runs on every component render)
    window.chartDataFromServer = {
        upp_id: {{ $upp_id ?? 'null' }},
        f02_labels: @json($f02_labels),
        f02_data: @json($f02_data),
        f03_labels: @json($f03_labels),
        f03_data: @json($f03_data),
        ipp_labels: @json($ipp_labels),
        ipp_data: @json($ipp_data),
        aspek_labels: @json($aspek_labels),
        aspek_values: @json($aspek_values)
    };
</script>
```

#### How Data Should Get Refreshed (But Doesn't)
```
1. User filters → Livewire.dispatch('setUppFilter', { upp_id: 1 })
2. PHP component SHOULD listen (needs #[On('setUppFilter')] or similar)
3. PHP component updates public properties: $this->upp_id = 1
4. Component re-renders (calls render())
5. Blade template executes with NEW data: @json($f02_data) etc.
6. Browser receives: window.chartDataFromServer = { ... new data ... }
7. livewire:updated event fires
8. JavaScript re-initializes charts

❌ PROBLEM: Step 3-6 might not be happening properly
```

#### JavaScript Waits for Update (But Data Never Changes)
```javascript
// Analytics: Line ~280+
document.addEventListener('livewire:updated', () => {
    console.log('');
    console.log('═══════════════════════════════════════════════════════');
    console.log('🔄 LIVEWIRE:UPDATED EVENT FIRED');
    console.log('═══════════════════════════════════════════════════════');

    if (window.chartDataFromServer) {
        console.log('📊 Current window.chartDataFromServer state:');
        console.log('   - upp_id:', window.chartDataFromServer.upp_id);  // Might still be null!
        console.log('   - f02_data length:', window.chartDataFromServer.f02_data?.length);  // Still old length!
        console.log('   - f03_data length:', window.chartDataFromServer.f03_data?.length);
        console.log('   - ipp_data length:', window.chartDataFromServer.ipp_data?.length);
        console.log('   - aspek_values length:', window.chartDataFromServer.aspek_values?.length);
    } else {
        console.error('❌ window.chartDataFromServer NOT FOUND!');
    }

    // Re-init charts with (same/old) data
    setTimeout(() => {
        try {
            console.log('🔄 Re-initializing all charts...');
            initF02Chart();      // ← Uses OLD window.chartDataFromServer
            initF03Chart();      // ← Uses OLD window.chartDataFromServer
            initIPPChart();      // ← Uses OLD window.chartDataFromServer
            initAspekChart();    // ← Uses OLD window.chartDataFromServer
        } catch (error) {
            console.error('❌ Error re-initializing charts:', error);
        }
    }, 100);
});
```

---

## PART 3: MODAL STRUCTURE DIFFERENCES

### 3.1 WORKING - Dashboard Modal (Simple Overlay)

```blade
{{-- Dashboard: Line ~1453-1497 --}}
<div class="modal-overlay" id="uppFilterModal">
    <div class="modal-dialog">
        <div class="modal-header">
            <h5><i class="fas fa-building"></i> Pilih Unit Pelayanan</h5>
            <button type="button" class="modal-close-btn" id="closeModal">&times;</button>
        </div>
        <div class="modal-body">
            <div class="select-all-check">
                <input class="form-check-input" type="checkbox" id="selectAllUpp">
                <label class="form-check-label" for="selectAllUpp">
                    Pilih Semua ({{ $availableUpps->count() }} UPP)
                </label>
            </div>
            <div id="uppChecklistContainer">
                @foreach($availableUppsWithScores as $upp)
                    <div class="form-check">
                        <input class="form-check-input upp-checkbox" type="checkbox" 
                            id="upp_{{ $upp['id'] }}" 
                            value="{{ $upp['id'] }}"
                            {{ in_array($upp['id'], $selectedUppIds) ? 'checked' : '' }}>
                        <label class="form-check-label" for="upp_{{ $upp['id'] }}">
                            <span>{{ $upp['nama'] }}</span>
                            <span style="...">{{ number_format($upp['ipp_score'], 2) }}</span>
                        </label>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-modal btn-modal-cancel" id="closeModalBtn">
                <i class="fas fa-times"></i> Batal
            </button>
            <button type="button" class="btn-modal btn-modal-submit" id="submitUppFilter">
                <i class="fas fa-check"></i> Tampilkan Data
            </button>
        </div>
    </div>
</div>

{{-- Hidden form for submission --}}
<form method="GET" id="filterForm" style="display: none;">
    <select name="upp_ids[]" id="uppSelect" multiple required>
        @foreach($availableUpps as $upp)
            <option value="{{ $upp->id }}"
                {{ in_array($upp->id, $selectedUppIds) ? 'selected' : '' }}>
                {{ $upp->nama }}
            </option>
        @endforeach
    </select>
</form>
```

**Key Points:**
- Modal is custom HTML overlay
- Form is included in the same template
- Form submission is the primary action
- Multiple selection is preserved through form submission

---

### 3.2 NON-WORKING - Analytics Modal (Same Structure, But Different Submission)

```blade
{{-- Analytics: Line ~120-160 --}}
<div class="modal-overlay" id="uppFilterModal">
    <div class="modal-dialog">
        <div class="modal-header">
            <h5><i class="fas fa-building"></i> Pilih Unit Pelayanan</h5>
            <button type="button" class="modal-close-btn" id="closeModal">&times;</button>
        </div>
        <div class="modal-body">
            <div class="select-all-check">
                <input class="form-check-input" type="checkbox" id="selectAllUpp">
                <label class="form-check-label" for="selectAllUpp">
                    Pilih Semua ({{ count($upp_options) }} UPP)
                </label>
            </div>
            <div id="uppChecklistContainer">
                @foreach($upp_options as $upp)
                    <div class="form-check">
                        <input class="form-check-input upp-checkbox" type="checkbox"
                            id="upp_{{ $upp['id'] }}"
                            value="{{ $upp['id'] }}"
                            {{ $upp_id == $upp['id'] ? 'checked' : '' }}>
                        <label class="form-check-label" for="upp_{{ $upp['id'] }}">
                            <span>{{ $upp['label'] }}</span>
                            <span>{{ $upp['id'] }}</span>
                        </label>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-modal btn-modal-cancel" id="closeModalBtn">
                <i class="fas fa-times"></i> Batal
            </button>
            <button type="button" class="btn-modal btn-modal-submit" id="submitUppFilter">
                <i class="fas fa-check"></i> Tampilkan Data
            </button>
        </div>
    </div>
</div>

{{-- Same form here --}}
<form method="GET" id="filterForm" style="display: none;">
    <select name="upp_ids[]" id="uppSelect" multiple required>
        @foreach($upp_options as $upp)
            <option value="{{ $upp['id'] }}" {{ $upp_id == $upp['id'] ? 'selected' : '' }}>
                {{ $upp['label'] }}
            </option>
        @endforeach
    </select>
</form>
```

**Key Differences:**
- Modal structure is nearly identical
- BUT submit handler dispatches to Livewire instead of form submission
- Takes only first selected value
- Relies on Livewire listener that may not exist

---

## PART 4: JAVASCRIPT EVENT HANDLING

### 4.1 WORKING - Dashboard (Complete Chain)

```javascript
// Dashboard: Sequential, guaranteed chain of events
submitModalBtn.addEventListener('click', function() {
    // 1. Collect ALL selections
    const selectedValues = Array.from(uppCheckboxes)
        .filter(cb => cb.checked)
        .map(cb => cb.value);  // [1, 2, 3]

    // 2. Validate
    if (selectedValues.length === 0) {
        alert('Pilih minimal satu UPP!');
        return;
    }

    // 3. Update form select
    const uppSelect = document.getElementById('uppSelect');
    Array.from(uppSelect.options).forEach(option => {
        option.selected = selectedValues.includes(option.value);
    });

    // 4. AJAX save (optional backup)
    fetch('{{ route("dashboard.save-preferred-upps") }}', {
        method: 'POST',
        body: JSON.stringify({ upp_ids: selectedValues })
    }).then(response => {
        // 5. Form submit (guaranteed, even if AJAX fails)
        document.getElementById('filterForm').submit();
    }).catch(error => {
        // 5. Form submit (even on error)
        document.getElementById('filterForm').submit();
    });
});
```

**Flow:** Collect → Validate → Update Form → AJAX (optional) → Form Submit → Page Reload → Fresh Data

---

### 4.2 NON-WORKING - Analytics (Broken Chain)

```javascript
// Analytics: Incomplete chain with missing listener
submitModalBtn.addEventListener('click', function() {
    // 1. Collect selections
    const selectedValues = Array.from(uppCheckboxes)
        .filter(cb => cb.checked)
        .map(cb => cb.value);  // [1, 2, 3]

    if (selectedValues.length === 0) {
        alert('Pilih minimal satu UPP!');
        return;
    }

    // ❌ 2a. ONLY TAKES FIRST
    const uppId = parseInt(selectedValues[0]);  // Only 1, lose 2 & 3!

    if (window.Livewire) {
        try {
            // 2b. Dispatch to Livewire
            Livewire.dispatch('setUppFilter', { upp_id: uppId });
            console.log('⏳ Waiting for PHP event handler and livewire:updated...');
        } catch (error) {
            console.error('❌ Error during dispatch:', error);
        }
    }

    // 3. Close modal
    closeModal();
    
    // ❌ MISSING: No guarantee that PHP handler exists
    // ❌ MISSING: No guarantee that window.chartDataFromServer updated
    // ❌ MISSING: Fallback if Livewire dispatch fails
});
```

**Flow:** Collect → Take First Only → Dispatch → Wait → (Listener Missing?) → (Data Never Updates?) → Charts Show Old Data

---

## PART 5: CHART INITIALIZATION & UPDATE

### 5.1 WORKING - Dashboard (Simple: Init on Load, Destroy on Filter)

```javascript
// Dashboard: Line ~1234
document.addEventListener('DOMContentLoaded', function() {
    if (chartDataCache.length > 0) {
        initializeCharts();
    }
    // ...setup modal listeners...
});

// When filter is applied → Form submit → Page reload
// Entire page HTML is replaced
// New script runs: const chartDataCache = @json($dashboardData['upps'])
// New DOMContentLoaded fires
// initializeCharts() runs with NEW data

function initializeCharts() {
    const labels = chartDataCache.map(d => getEmailPrefix(d.user_email));
    const f02Data = chartDataCache.map(d => parseFloat(d.f02_nilai));
    const f03Data = chartDataCache.map(d => parseFloat(d.f03_rata_rata));
    // ... create charts with labels and data ...
}
```

---

### 5.2 NON-WORKING - Analytics (Complex: Init on Load, Try to Update, But Data Never Changes)

```javascript
// Analytics: Line ~260+
document.addEventListener('DOMContentLoaded', () => {
    console.log('🚀 DOMContentLoaded - initializing modal & charts...');
    
    try {
        console.log('📊 Initializing charts...');
        initF02Chart();   // ← Reads window.chartDataFromServer
        initF03Chart();   // ← Reads window.chartDataFromServer
        initIPPChart();   // ← Reads window.chartDataFromServer
        initAspekChart(); // ← Reads window.chartDataFromServer
    } catch (error) {
        console.error('❌ Error initializing charts:', error);
    }
});

// ❌ PROBLEM: This event fires after Livewire dispatch, but data is still old
document.addEventListener('livewire:updated', () => {
    console.log('🔄 LIVEWIRE:UPDATED EVENT FIRED');

    // Check data - ❌ Still shows old values!
    if (window.chartDataFromServer) {
        console.log('   - upp_id:', window.chartDataFromServer.upp_id);  // Still old!
        console.log('   - f02_data length:', window.chartDataFromServer.f02_data?.length);  // Still old!
    }

    // Try to re-init charts with old data
    setTimeout(() => {
        try {
            initF02Chart();   // ← Still old data!
            initF03Chart();   // ← Still old data!
            initIPPChart();   // ← Still old data!
            initAspekChart(); // ← Still old data!
        } catch (error) {
            console.error('❌ Error re-initializing charts:', error);
        }
    }, 100);
});

// Helper that reads from window.chartDataFromServer
function getChartDataFromAttributes() {
    if (window.chartDataFromServer) {
        return {
            f02_labels: window.chartDataFromServer.f02_labels || [],
            f02_data: window.chartDataFromServer.f02_data || [],
            // ... etc
        };
    }
    // ... fallback to data attributes
}

function initF02Chart() {
    const ctx = document.getElementById('f02Chart');
    const chartData = getChartDataFromAttributes();  // ❌ Gets OLD data
    // Create chart with old data
}
```

---

## PART 6: ROOT CAUSES IDENTIFIED

### Issue #1: Missing Livewire Event Listener

**Dashboard**: No PHP listener needed (uses form submission)

**Analytics**: Expects PHP component to have:
```php
// MISSING IN ANALYTICS COMPONENT!
#[On('setUppFilter')]
public function setUppFilter($upp_id)
{
    $this->upp_id = $upp_id;
    // Re-fetch data with new UPP filter
    $this->loadChartData();
}
```

**Result**: JavaScript dispatches event, but PHP never handles it

---

### Issue #2: Window Object Not Updated After Livewire Render

**Dashboard**: New page HTML includes: `const chartDataCache = @json($dashboardData['upps'])`

**Analytics**: Blade includes: `window.chartDataFromServer = @json($f02_data)`

**Problem**: After Livewire updates, new HTML is rendered, but JavaScript might not re-execute this assignment or updates stale reference.

---

### Issue #3: Only First Selected UPP Used

**Dashboard**: 
```javascript
Array.from(uppSelect.options).forEach(option => {
    option.selected = selectedValues.includes(option.value);  // ALL selected
});
```

**Analytics**:
```javascript
const uppId = parseInt(selectedValues[0]);  // ONLY FIRST!
Livewire.dispatch('setUppFilter', { upp_id: uppId });
```

**Result**: Multiple UPP selection capability is ignored

---

### Issue #4: No Fallback Mechanism

**Dashboard**:
```javascript
fetch(...).then(...).catch(error => {
    document.getElementById('filterForm').submit();  // Fallback works!
});
```

**Analytics**:
```javascript
Livewire.dispatch('setUppFilter', { upp_id: uppId });
console.log('⏳ Waiting for PHP event handler...');
// ❌ NO FALLBACK if dispatch fails or handler missing!
```

**Result**: If Livewire event dispatch fails, nothing happens

---

## PART 7: SIDE-BY-SIDE CODE FLOW COMPARISON

### Dashboard (WORKING)
```
┌─────────────────────────────────────────────────────────────┐
│ User clicks "Tampilkan Data" button                         │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ↓
┌─────────────────────────────────────────────────────────────┐
│ JavaScript collects ALL selected UPP IDs                    │
│ selectedValues = [1, 2, 3]                                  │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ↓
┌─────────────────────────────────────────────────────────────┐
│ Updates hidden form select with all selected options        │
│ uppSelect.options.selected = [true, true, true, ...]       │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ↓
┌─────────────────────────────────────────────────────────────┐
│ AJAX POST to /dashboard/save-preferred-upps                │
│ { upp_ids: [1, 2, 3] }                                      │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ↓ (success or error)
┌─────────────────────────────────────────────────────────────┐
│ Form submission (in both success and error)                 │
│ document.getElementById('filterForm').submit()              │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ↓
┌─────────────────────────────────────────────────────────────┐
│ HTTP GET request to /dashboard?upp_ids[]=1&upp_ids[]=2...   │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ↓
┌─────────────────────────────────────────────────────────────┐
│ SERVER SIDE: Controller processes filter                    │
│ $selectedUppIds = $request->input('upp_ids', []);          │
│ $dashboardData = $this->getFilteredData($selectedUppIds);  │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ↓
┌─────────────────────────────────────────────────────────────┐
│ Blade renders entire page with NEW filtered data            │
│ chartDataCache = @json($dashboardData['upps']) ← NEW!       │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ↓
┌─────────────────────────────────────────────────────────────┐
│ Browser receives complete HTML                              │
│ Page re-renders, scripts execute                            │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ↓
┌─────────────────────────────────────────────────────────────┐
│ DOMContentLoaded fires                                       │
│ initializeCharts() called with NEW data                     │
│ Charts display NEW data ✓                                    │
└─────────────────────────────────────────────────────────────┘
```

### Analytics (NON-WORKING)
```
┌─────────────────────────────────────────────────────────────┐
│ User clicks "Tampilkan Data" button                         │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ↓
┌─────────────────────────────────────────────────────────────┐
│ JavaScript collects selected UPP IDs                        │
│ selectedValues = [1, 2, 3]                                  │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ↓ ❌ TAKES ONLY FIRST!
┌─────────────────────────────────────────────────────────────┐
│ const uppId = parseInt(selectedValues[0]) → 1              │
│ (loses 2 and 3!)                                             │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ↓
┌─────────────────────────────────────────────────────────────┐
│ Livewire.dispatch('setUppFilter', { upp_id: 1 })          │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ↓ ❌ WAITING FOR LISTENER
┌─────────────────────────────────────────────────────────────┐
│ JavaScript waits for:                                        │
│   - PHP #[On('setUppFilter')] handler                       │
│   - Livewire re-render                                      │
│   - window.chartDataFromServer update                       │
│   - livewire:updated event                                   │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ↓ ❌ LISTENER MISSING OR DATA NOT UPDATED
┌─────────────────────────────────────────────────────────────┐
│ livewire:updated fires (maybe)                              │
│ But window.chartDataFromServer still has OLD data!          │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ↓
┌─────────────────────────────────────────────────────────────┐
│ Charts re-init with OLD data:                               │
│ initF02Chart() reads window.chartDataFromServer ← OLD!     │
│ initF03Chart() reads window.chartDataFromServer ← OLD!     │
│ initIPPChart() reads window.chartDataFromServer ← OLD!     │
│ initAspekChart() reads window.chartDataFromServer ← OLD!   │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ↓
┌─────────────────────────────────────────────────────────────┐
│ Charts display OLD data ✗                                    │
│ No filter change visible!                                   │
└─────────────────────────────────────────────────────────────┘
```

---

## SUMMARY: KEY DIFFERENCES

| Aspect | Dashboard (✓ Working) | Analytics (✗ Not Working) |
|--------|----------------------|---------------------------|
| **Event Mechanism** | Form submission + page reload | Livewire dispatch event |
| **Data Update** | Guaranteed via page reload | Depends on PHP listener + window update |
| **Selection Mode** | All selected UPPs preserved | Only first UPP taken |
| **Data Source** | `chartDataCache` (re-embedded on reload) | `window.chartDataFromServer` (should update) |
| **Chart Re-render** | Automatic on page load | Manual via `livewire:updated` listener |
| **Fallback** | AJAX + form both work | No fallback if dispatch fails |
| **Chart Instances** | Destroyed on page reload | Manually destroyed in JS |
| **State Management** | Server-side (URL params + session) | Component properties |
| **Update Confirmation** | Page reload = guaranteed | Event listener may not exist |

---

## RECOMMENDATION: Quick Fixes

### For Analytics Panel:
1. **Add PHP event listener**: Ensure `#[On('setUppFilter')]` method exists
2. **Update multiple UPPs**: Pass array instead of single ID
3. **Debug window update**: Add logging to verify `window.chartDataFromServer` changes
4. **Add fallback**: If Livewire dispatch fails, try alternative approach
5. **Test chain**: Verify each step (dispatch → listener → re-render → window update → event)

### For Immediate Testing:
Add this to analytics panel's PHP component:
```php
#[On('setUppFilter')]
public function setUppFilter($upp_id)
{
    \Log::info('setUppFilter called with upp_id: ' . $upp_id);
    $this->upp_id = $upp_id;
    $this->loadChartData();
}
```

Then check logs to see if listener is being called.
