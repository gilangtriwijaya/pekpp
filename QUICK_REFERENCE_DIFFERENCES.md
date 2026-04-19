# QUICK REFERENCE: CODE DIFFERENCES - Dashboard vs Analytics

## FILE LOCATIONS
- **WORKING**: `/home/deploy/apps/pekpp/resources/views/dashboard/index.blade.php` (2076 lines)
- **BROKEN**: `/home/deploy/apps/pekpp/resources/views/livewire/analytics/panel.blade.php` (~1200 lines)

---

## DIFFERENCE #1: MODAL SUBMIT HANDLER

### ✅ DASHBOARD (WORKS)
**File**: `dashboard/index.blade.php` (Lines 1210-1250)

```javascript
submitModalBtn.addEventListener('click', function() {
    const selectedValues = Array.from(uppCheckboxes)
        .filter(cb => cb.checked)
        .map(cb => cb.value);

    if (selectedValues.length === 0) {
        alert('Pilih minimal satu UPP!');
        return;
    }

    // 1. Update form values
    const uppSelect = document.getElementById('uppSelect');
    Array.from(uppSelect.options).forEach(option => {
        option.selected = selectedValues.includes(option.value);
    });

    // 2. Save preference (optional)
    fetch('{{ route("dashboard.save-preferred-upps") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify({ upp_ids: selectedValues })
    }).then(response => {
        // 3. CRITICAL: Submit form for page reload
        document.getElementById('filterForm').submit();
    }).catch(error => {
        console.error('Error saving preference:', error);
        // Still submit even if save failed
        document.getElementById('filterForm').submit();
    });
});
```

**Key Points**:
- ✅ Updates form values in the DOM
- ✅ Saves preference (non-critical, doesn't block)
- ✅ **ALWAYS submits form** → Page reloads with GET params
- ✅ Server processes and returns fresh data

---

### ❌ ANALYTICS PANEL (BROKEN)
**File**: `livewire/analytics/panel.blade.php` (Lines 690-730)

```javascript
submitModalBtn.addEventListener('click', function() {
    console.log('--- MODAL SUBMIT CLICKED ---');
    const selectedValues = Array.from(uppCheckboxes)
        .filter(cb => cb.checked)
        .map(cb => cb.value);

    console.log('✓ Selected checkboxes:', selectedValues.length);

    if (selectedValues.length === 0) {
        alert('Pilih minimal satu UPP!');
        return;
    }

    const uppId = parseInt(selectedValues[0]);
    console.log('✓ Taking first UPP ID:', uppId);

    // ❌ CRITICAL PROBLEM: Dispatch event to Livewire
    if (window.Livewire) {
        try {
            console.log('🔄 >>> DISPATCHING setUppFilter with upp_id:', uppId);
            
            // This event is dispatched but...
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

**Key Problems**:
- ❌ Dispatches Livewire event: `setUppFilter`
- ❌ **NO EVENT LISTENER** defined in component to catch this
- ❌ Event fires into void, component never updates
- ❌ Form is not submitted, page doesn't reload
- ❌ Data never refreshes

---

## DIFFERENCE #2: WHAT THE LIVEWIRE COMPONENT NEEDS

### Expected Implementation (MISSING!)

The Livewire component should have this listener:

```php
// In AnalyticsPanel.php (or wherever component is defined)

use Livewire\Attributes\On;

class AnalyticsPanel extends Component
{
    public $upp_id;
    public $f02_data = [];
    public $f02_labels = [];
    // ... other properties

    // ❌ THIS IS MISSING - Event listener
    #[On('setUppFilter')]
    public function setUppFilter($upp_id)
    {
        $this->upp_id = $upp_id;
        $this->loadChartData();  // Re-fetch data
    }

    private function loadChartData()
    {
        // Re-fetch data based on new $this->upp_id
        // This would update all the chart data properties
        // which triggers Blade template re-render
        // which updates window.chartDataFromServer
        // which triggers chart re-initialization
    }

    public function render()
    {
        return view('livewire.analytics.panel', [
            'f02_data' => $this->f02_data,
            'f02_labels' => $this->f02_labels,
            // ... other chart data
        ]);
    }
}
```

**Without this listener**, the JavaScript dispatch call has nowhere to go!

---

## DIFFERENCE #3: DATA FLOW

### ✅ DASHBOARD: Request → Server → Response → Render

```
User clicks "Tampilkan Data"
           ↓
JavaScript collects selected UPPs
           ↓
Form updated with selected values
           ↓
filterForm.submit() ← GET request to server
           ↓
URL: ?upp_ids[]=1&upp_ids[]=2&upp_ids[]=3
           ↓
Server receives query parameters
           ↓
Controller method processes:
  $selectedUppIds = $request->get('upp_ids', []);
  $data = fetchDashboardData($selectedUppIds);
           ↓
View renders with FRESH DATA
           ↓
window.chartDataCache = [...fresh data...]
  OR
AJAX fetch returns fresh data
           ↓
Charts initialized with new data
           ↓
✅ CHARTS UPDATE - User sees new data
```

---

### ❌ ANALYTICS PANEL: Event → ??? → Error

```
User clicks "Tampilkan Data"
           ↓
JavaScript collects selected UPPs
           ↓
Livewire.dispatch('setUppFilter', { upp_id: 1 })
           ↓
JavaScript console shows:
  ✓ >>> Dispatch call completed
  ⏳ Waiting for PHP event handler and livewire:updated...
           ↓
❌ Event arrives at Livewire but...
   NO EVENT LISTENER defined!
           ↓
❌ Component method setUppFilter() doesn't exist
   or doesn't have @[On('setUppFilter')] attribute
           ↓
❌ Component properties never update
           ↓
❌ Template never re-renders
           ↓
❌ window.chartDataFromServer never updates
           ↓
❌ livewire:updated fires but data is UNCHANGED
           ↓
Charts re-initialize with OLD DATA
           ↓
❌ CHARTS DON'T UPDATE - User sees nothing change
```

---

## DIFFERENCE #4: CHART RE-RENDERING

### ✅ DASHBOARD: Manual Chart Management

**When chart filter applied** (Lines 1400-1450):

```javascript
function fetchAndRenderComparison() {
    // Step 1: Fetch fresh data from API
    fetch('{{ route("api.dashboard.filtered-data") }}', {
        method: 'POST',
        body: JSON.stringify({
            upp_ids: comparisonFilterUppIds  // Selected UPP IDs
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Step 2: Update local cache with FRESH DATA
            chartDataCache = data.upps;
            
            // Step 3: Render chart with new data
            renderComparisonChartFiltered();
        }
    });
}

function renderComparisonChartFiltered() {
    // Step 4: Destroy old chart
    if (comparisonChart) comparisonChart.destroy();
    
    // Step 5: Create new chart with fresh data
    comparisonChart = new Chart(ctxComparison, {
        data: {
            labels: labels,
            datasets: [{
                label: 'F02 (Dokumentasi)',
                data: f02Data,  // ← FRESH DATA
                // ...
            }]
        }
    });
}
```

**Result**: ✅ Fresh data from API → New chart immediately visible

---

### ❌ ANALYTICS PANEL: Dependent on Component Update

**Chart initialization function** (Lines 780-850):

```javascript
function initF02Chart() {
    const ctx = document.getElementById('f02Chart');
    if (!ctx) return;

    // Get data from window object (set by Blade template)
    const chartData = getChartDataFromAttributes();
    if (!chartData) {
        console.error('❌ Could not get chart data from attributes');
        return;
    }

    // Destroy old chart
    if (chartInstances.f02Chart) {
        chartInstances.f02Chart.destroy();
    }

    // Create new chart with data from window object
    chartInstances.f02Chart = new Chart(ctx, {
        data: {
            labels: chartData.f02_labels,
            datasets: [{
                label: 'Skor F02',
                data: chartData.f02_data,  // ← FROM window.chartDataFromServer
                // ...
            }]
        }
    });
}

function getChartDataFromAttributes() {
    // ❌ PROBLEM: This data only updates when component re-renders
    if (window.chartDataFromServer) {
        return {
            f02_data: window.chartDataFromServer.f02_data || [],  // Still OLD data!
            // ...
        };
    }
    // ...
}
```

**Called from** (Lines 970-1000):

```javascript
document.addEventListener('livewire:updated', () => {
    // ⏳ Waits for component to re-render
    // ❌ But component never re-renders because event listener missing!
    
    setTimeout(() => {
        initF02Chart();  // Re-initializes with OLD DATA
    }, 100);
});
```

**Result**: ❌ Old data persists → Chart shows old data

---

## DIFFERENCE #5: BLADE TEMPLATE VARIABLES

### ✅ DASHBOARD: Server-Side Rendering on Page Load

```blade
@if(!empty($dashboardData['upps']))
    <div class="stats-container">
        @if($isGlobalUser)
            <div class="stat-card primary">
                <div class="stat-label">Total Unit Pelayanan</div>
                <div class="stat-value">{{ $dashboardData['summary']['total_upp'] }}</div>
            </div>
            <!-- ... 9 more stat cards ... -->
        @endif
    </div>

    <div class="charts-grid">
        <div class="chart-card">
            <div class="chart-header">
                <div class="chart-title">Perbandingan F02 & F03</div>
                <!-- ... chart buttons ... -->
            </div>
            <div class="chart-container">
                <canvas id="comparisonChart"></canvas>
            </div>
        </div>
        <!-- ... more charts ... -->
    </div>
@else
    <div class="empty-state">
        <i class="fas fa-inbox"></i>
        <h5>Tidak Ada Data</h5>
    </div>
@endif
```

**When filter applied**: Page reloads, all `$dashboardData` variables updated from server

---

### ❌ ANALYTICS PANEL: JavaScript Injection from Blade

```javascript
// Blade template runs ONCE (on component render)
window.chartDataFromServer = {
    upp_id: {{ $upp_id ?? 'null' }},                      // Set once by Blade
    f02_labels: @json($f02_labels),                       // Set once by Blade
    f02_data: @json($f02_data),                           // Set once by Blade
    f03_labels: @json($f03_labels),                       // Set once by Blade
    f03_data: @json($f03_data),                           // Set once by Blade
    ipp_labels: @json($ipp_labels),                       // Set once by Blade
    ipp_data: @json($ipp_data),                           // Set once by Blade
    aspek_labels: @json($aspek_labels),                   // Set once by Blade
    aspek_values: @json($aspek_values)                    // Set once by Blade
};
```

**Problem**: 
- ❌ Only updated when Livewire component re-renders
- ❌ Component never re-renders (event listener missing)
- ❌ Window object never updates
- ❌ Charts always show first render's data

---

## DIFFERENCE #6: ERROR HANDLING

### ✅ DASHBOARD: Explicit Error Messages

```javascript
function fetchAndRenderComparison() {
    fetch('{{ route("api.dashboard.filtered-data") }}', {
        // ... fetch config
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            chartDataCache = data.upps;
            renderComparisonChartFiltered();
        } else {
            alert('Error: ' + (data.error || 'Gagal memuat data'));  // ← User sees error
        }
    })
    .catch(error => {
        console.error('Error fetching data:', error);
        alert('Gagal mengambil data dari server');  // ← User sees error
    });
}
```

---

### ❌ ANALYTICS PANEL: Silent Failure

```javascript
Livewire.dispatch('setUppFilter', { upp_id: uppId });

console.log('✓ >>> Dispatch call completed');
console.log('⏳ Waiting for PHP event handler and livewire:updated...');

closeModal();
```

**Result**: 
- ❌ No error message shown
- ❌ User thinks it worked
- ❌ Charts don't update
- ❌ Console shows dispatch succeeded but nothing happens
- ❌ Only developer sees the problem in browser console

---

## QUICK FIX COMPARISON

### Option A: Convert Analytics to Dashboard Pattern (5 min)

Replace this:
```javascript
Livewire.dispatch('setUppFilter', { upp_id: uppId });
closeModal();
```

With this:
```javascript
// Update form and submit (like Dashboard)
document.getElementById('uppSelect').value = uppId;
document.getElementById('filterForm').submit();
closeModal();
```

### Option B: Add Livewire Event Listener (15 min)

Add to Livewire component:
```php
#[On('setUppFilter')]
public function setUppFilter($upp_id)
{
    $this->upp_id = $upp_id;
    // Re-fetch chart data
}
```

---

## SUMMARY TABLE

| Aspect | Dashboard | Analytics |
|--------|-----------|-----------|
| **Event Type** | Form submission | Livewire dispatch |
| **Data Flow** | Request → Server → Response | Event → ❌ Nowhere |
| **Chart Update** | Immediate, reliable | Dependent on component update |
| **Error Handling** | ✅ Explicit messages | ❌ Silent failure |
| **User Experience** | ✅ Charts update instantly | ❌ Nothing happens |
| **Root Cause** | N/A (works) | Missing event listener |
| **Line Count** | 2076 | ~1200 |
| **Status** | ✅ WORKS | ❌ BROKEN |

---

## FILES CREATED FOR DEBUGGING
1. `DETAILED_FILTER_COMPARISON.md` - Full analysis
2. `QUICK_REFERENCE_DIFFERENCES.md` - This file
3. Check `/memories/session/filter_comparison_analysis.md` for session notes
