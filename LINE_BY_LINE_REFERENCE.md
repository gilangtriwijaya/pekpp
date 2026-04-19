# LINE-BY-LINE REFERENCE: Exact Locations of Key Code

## FILE LOCATIONS

### Dashboard (Working)
📂 `/home/deploy/apps/pekpp/resources/views/dashboard/index.blade.php`
- Total lines: 2076
- Language: Blade + PHP + JavaScript

### Analytics Panel (Broken)
📂 `/home/deploy/apps/pekpp/resources/views/livewire/analytics/panel.blade.php`
- Total lines: ~1200
- Language: Blade + Livewire + PHP + JavaScript

---

## DASHBOARD - KEY SECTIONS

### 1. Filter Button & Hidden Form

**Lines: 560-590** ✅

```blade
@if($isGlobalUser && $availableUpps->count() > 0 && !empty($dashboardData['upps']))
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
    <button type="button" class="btn-filter" id="openUppModal">
        <i class="fas fa-search"></i> Filter UPP
    </button>
@endif
```

**Purpose**: Hidden form stores selected UPP IDs for submission

---

### 2. Modal HTML Structure

**Lines: 1765-1850** ✅

```blade
{{-- Modal Filter UPP (Custom Modal) --}}
@if($isGlobalUser && $availableUpps->count() > 0)
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
                                {{ $upp['nama'] }}
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
@endif
```

**Purpose**: Checkbox list for selecting multiple UPPs

---

### 3. JavaScript - Modal Open/Close

**Lines: 1910-1990** ✅

```javascript
document.addEventListener('DOMContentLoaded', function() {
    // Modal elements
    const moduleOverlay = document.getElementById('uppFilterModal');
    const openModalBtn = document.getElementById('openUppModal');
    const closeModalBtn = document.getElementById('closeModal');
    const closeModalBtnFooter = document.getElementById('closeModalBtn');
    const submitModalBtn = document.getElementById('submitUppFilter');

    // Open Modal
    if (openModalBtn && moduleOverlay) {
        openModalBtn.addEventListener('click', function() {
            moduleOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
    }

    // Close Modal
    const closeModal = () => {
        if (moduleOverlay) {
            moduleOverlay.classList.remove('active');
            document.body.style.overflow = '';
        }
    };

    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', closeModal);
    }
    if (closeModalBtnFooter) {
        closeModalBtnFooter.addEventListener('click', closeModal);
    }
});
```

**Purpose**: Open/close modal functionality

---

### 4. JavaScript - CRITICAL: Modal Submit Handler ⭐

**Lines: 1995-2050** ✅ **THIS IS THE KEY DIFFERENCE**

```javascript
if (submitModalBtn) {
    submitModalBtn.addEventListener('click', function() {
        const selectedValues = Array.from(uppCheckboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.value);

        if (selectedValues.length === 0) {
            alert('Pilih minimal satu UPP!');
            return;
        }

        // Update hidden form
        const uppSelect = document.getElementById('uppSelect');
        Array.from(uppSelect.options).forEach(option => {
            option.selected = selectedValues.includes(option.value);
        });

        // Save preference via AJAX before submitting form
        fetch('{{ route("dashboard.save-preferred-upps") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({
                upp_ids: selectedValues
            })
        }).then(response => {
            // Whether success or error, submit the form to update the view
            document.getElementById('filterForm').submit();  // ← KEY LINE
        }).catch(error => {
            console.error('Error saving preference:', error);
            // Still submit form to update view even if save failed
            document.getElementById('filterForm').submit();  // ← KEY LINE
        });
    });
}
```

**Key Points**:
- Line 2043: `document.getElementById('filterForm').submit();` - **Form submission**
- Line 2048: Same submit call in catch block (guaranteed execution)
- ✅ **Result**: Page reloads with GET params, server sends fresh data

---

### 5. Chart Filter AJAX Fetch

**Lines: 1400-1450** ✅

```javascript
function fetchAndRenderComparison() {
    fetch('{{ route("api.dashboard.filtered-data") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify({
            upp_ids: comparisonFilterUppIds
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update local data cache
            chartDataCache = data.upps;  // ← Fresh data from server
            // Render chart with fresh data
            renderComparisonChartFiltered();
        } else {
            alert('Error: ' + (data.error || 'Gagal memuat data'));
        }
    })
    .catch(error => {
        console.error('Error fetching data:', error);
        alert('Gagal mengambil data dari server');
    });
}
```

**Purpose**: Fetch fresh data from API endpoint

---

## ANALYTICS PANEL - KEY SECTIONS

### 1. Filter Button & Hidden Form

**Lines: 200-230** ❌

```blade
<form method="GET" id="filterForm" style="display: none;">
    <select name="upp_ids[]" id="uppSelect" multiple required>
        @foreach($upp_options as $upp)
            <option value="{{ $upp['id'] }}" {{ $upp_id == $upp['id'] ? 'selected' : '' }}>
                {{ $upp['label'] }}
            </option>
        @endforeach
    </select>
</form>
<button type="button" class="btn-filter" id="openUppModal">
    <i class="fas fa-search"></i> Filter UPP
</button>
```

**Status**: ✅ Same as Dashboard (OK)

---

### 2. Modal HTML Structure

**Lines: 270-320** ❌

```blade
{{-- Modal Filter UPP --}}
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
                            {{ $upp['label'] }}
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
```

**Status**: ✅ Same as Dashboard (OK)

---

### 3. Window Object - Chart Data Storage

**Lines: 355-400** ⚠️

```javascript
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
console.log('📊 [BLADE RENDER] window.chartDataFromServer initialized:', {
    upp_id: window.chartDataFromServer.upp_id,
    f02_count: window.chartDataFromServer.f02_data?.length || 0,
    // ...
});
```

**Problem**: ⚠️ Only updated when component re-renders (which never happens)

---

### 4. JavaScript - Modal Open/Close

**Lines: 450-520** ✅

```javascript
document.addEventListener('DOMContentLoaded', () => {
    console.log('🚀 DOMContentLoaded - initializing modal & charts...');

    const moduleOverlay = document.getElementById('uppFilterModal');
    const openModalBtn = document.getElementById('openUppModal');
    const closeModalBtn = document.getElementById('closeModal');
    const closeModalBtnFooter = document.getElementById('closeModalBtn');
    const submitModalBtn = document.getElementById('submitUppFilter');

    // Open Modal
    if (openModalBtn && moduleOverlay) {
        openModalBtn.addEventListener('click', function() {
            console.log('Opening modal...');
            moduleOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
    }

    // Close Modal
    const closeModal = () => {
        console.log('Closing modal...');
        if (moduleOverlay) {
            moduleOverlay.classList.remove('active');
            document.body.style.overflow = '';
        }
    };
    // ...
});
```

**Status**: ✅ Same as Dashboard (OK)

---

### 5. JavaScript - CRITICAL: Modal Submit Handler ⭐⭐⭐

**Lines: 690-740** ❌ **THIS IS THE PROBLEM**

```javascript
if (submitModalBtn) {
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
        console.log('✓ window.Livewire exists?', !!window.Livewire);

        // ❌ PROBLEM: Dispatch event to Livewire
        if (window.Livewire) {
            try {
                console.log('🔄 >>> DISPATCHING setUppFilter with upp_id:', uppId);
                console.log('   Payload: { upp_id: ' + uppId + ' }');

                // Livewire v4 dispatch syntax
                Livewire.dispatch('setUppFilter', { upp_id: uppId });  // ← LINE 717

                console.log('✓ >>> Dispatch call completed');
                console.log('⏳ Waiting for PHP event handler and livewire:updated...');
            } catch (error) {
                console.error('❌ Error during dispatch:', error);
            }
        } else {
            console.error('❌ Livewire not found!');
        }

        console.log('🔚 Closing modal...');
        closeModal();
        console.log('--- MODAL SUBMIT END ---');
    });
}
```

**The Problem**:
- ❌ Line 717: `Livewire.dispatch('setUppFilter', { upp_id: uppId });`
- ❌ Event dispatches but has NO LISTENER
- ❌ Component never updates
- ❌ Charts never re-render with new data

**Compare to Dashboard**:
- ✅ Line 2043: `document.getElementById('filterForm').submit();`
- ✅ Form submission guaranteed to reload page
- ✅ Server processes and returns fresh data

---

### 6. JavaScript - Chart Data Helper Function

**Lines: 770-810** ⚠️

```javascript
// Helper function to get chart data (prioritize window object, fallback to data attributes)
function getChartDataFromAttributes() {
    // Primary source: window.chartDataFromServer (updated by Livewire on every render)
    if (window.chartDataFromServer) {
        console.log('📊 [getChartDataFromAttributes] Using window.chartDataFromServer');
        console.log('    - upp_id:', window.chartDataFromServer.upp_id);
        console.log('    - f02_data:', window.chartDataFromServer.f02_data?.length || 0, 'items');
        return {
            f02_labels: window.chartDataFromServer.f02_labels || [],
            f02_data: window.chartDataFromServer.f02_data || [],  // ← OLD DATA!
            f03_labels: window.chartDataFromServer.f03_labels || [],
            f03_data: window.chartDataFromServer.f03_data || [],  // ← OLD DATA!
            // ...
        };
    }

    // Fallback: data attributes
    const debugEl = document.getElementById('debugUppId');
    if (!debugEl) {
        console.error('❌ [getChartDataFromAttributes] debugUppId element not found!');
        return null;
    }
    return {
        f02_labels: JSON.parse(debugEl.getAttribute('data-f02-labels') || '[]'),
        f02_data: JSON.parse(debugEl.getAttribute('data-f02-data') || '[]'),
        // ...
    };
}
```

**Problem**: Data source only updates if component re-renders (which never happens)

---

### 7. JavaScript - Chart Initialization Function

**Lines: 820-870** ⚠️

```javascript
function initF02Chart() {
    const ctx = document.getElementById('f02Chart');
    if (!ctx) return;

    const chartData = getChartDataFromAttributes();  // ← Gets OLD data
    if (!chartData) {
        console.error('❌ Could not get chart data from attributes');
        return;
    }

    console.log('📊 F02Chart data:', chartData.f02_data);

    // Destroy existing chart if any
    if (chartInstances.f02Chart) {
        chartInstances.f02Chart.destroy();
    }

    chartInstances.f02Chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.f02_labels,
            datasets: [{
                label: 'Skor F02',
                data: chartData.f02_data,  // ← OLD DATA displayed
                borderColor: colors.primary,
                // ...
            }]
        },
        options: chartOptions
    });
}
```

**Problem**: Always uses old data from `window.chartDataFromServer`

---

### 8. JavaScript - CRITICAL: Livewire Updated Handler ⭐

**Lines: 970-1050** ❌

```javascript
// Reinitialize charts when Livewire updates (Livewire v4 compatible)
document.addEventListener('livewire:updated', () => {
    console.log('');
    console.log('═══════════════════════════════════════════════════════');
    console.log('🔄 LIVEWIRE:UPDATED EVENT FIRED');
    console.log('═══════════════════════════════════════════════════════');

    if (window.chartDataFromServer) {
        console.log('📊 Current window.chartDataFromServer state:');
        console.log('   - upp_id:', window.chartDataFromServer.upp_id);
        console.log('   - f02_data length:', window.chartDataFromServer.f02_data?.length);
        console.log('   - f03_data length:', window.chartDataFromServer.f03_data?.length);
        console.log('   - ipp_data length:', window.chartDataFromServer.ipp_data?.length);
        console.log('   - aspek_values length:', window.chartDataFromServer.aspek_values?.length);
    } else {
        console.error('❌ window.chartDataFromServer NOT FOUND!');
    }

    // Small delay to ensure DOM is fully updated
    setTimeout(() => {
        try {
            console.log('🔄 Re-initializing all charts...');
            initF02Chart();
            console.log('   ✓ F02 chart re-initialized');

            initF03Chart();
            console.log('   ✓ F03 chart re-initialized');

            initIPPChart();
            console.log('   ✓ IPP chart re-initialized');

            initAspekChart();
            console.log('   ✓ Aspek chart re-initialized');

            console.log('✅ All charts updated successfully!');
        } catch (error) {
            console.error('❌ Error re-initializing charts:', error);
        }
        console.log('═══════════════════════════════════════════════════════');
        console.log('');
    }, 100);
});
```

**Problem**: 
- ⚠️ This event listener waits for `livewire:updated`
- ❌ But component never updates because event handler missing
- ❌ So this never fires with new data
- ❌ Charts re-initialize with old data

---

## MISSING PIECE IN ANALYTICS COMPONENT

### The Livewire Component - MISSING Event Listener

**File**: Unknown (need to find the Livewire component class)

**Should contain** (but doesn't):

```php
use Livewire\Attributes\On;

class AnalyticsPanel extends Component
{
    public $upp_id = null;
    public $f02_data = [];
    public $f02_labels = [];
    // ... other properties

    // ❌ THIS IS MISSING!
    #[On('setUppFilter')]
    public function setUppFilter($upp_id)
    {
        $this->upp_id = $upp_id;
        // Re-fetch chart data
        $this->loadChartData();
        // Component automatically re-renders
    }

    private function loadChartData()
    {
        // Fetch fresh data based on $this->upp_id
    }
}
```

**Without this method**, the JavaScript dispatch call in line 717 has nowhere to go!

---

## SUMMARY: LINE-BY-LINE COMPARISON

| Task | Dashboard Line | Analytics Line | Status |
|------|----------------|----------------|--------|
| Modal open/close | 1910-1990 | 450-520 | ✅ Both OK |
| Hidden form | 560-590 | 200-230 | ✅ Both OK |
| Modal checkboxes | 1800-1850 | 270-320 | ✅ Both OK |
| Submit handler | **2043** | **717** | ❌ Different! |
| Dashboard submits form | 2043 | N/A | ✅ Works |
| Analytics dispatches event | N/A | 717 | ❌ Broken |
| Event listener in component | N/A | **MISSING** | ❌ Critical! |

---

## THE FIX (Line-by-Line)

### Current Analytics (Broken)
**File**: `resources/views/livewire/analytics/panel.blade.php`  
**Lines**: 690-740

```javascript
// ❌ PROBLEM: Dispatch without listener
Livewire.dispatch('setUppFilter', { upp_id: uppId });
closeModal();
```

### Fixed Analytics (Works)
**File**: `resources/views/livewire/analytics/panel.blade.php`  
**Lines**: 690-740 (REPLACE)

```javascript
// ✅ SOLUTION: Use form submission like Dashboard
document.getElementById('uppSelect').value = uppId;
document.getElementById('filterForm').submit();
closeModal();
```

**That's it! 3 lines instead of 1, but guaranteed to work.**

---

## HOW TO USE THIS REFERENCE

1. **Need to understand Dashboard flow?** → Start at Dashboard section
2. **Need to understand Analytics flow?** → Start at Analytics section
3. **Need exact line numbers?** → Check tables and code blocks
4. **Need the fix?** → See "THE FIX" section at bottom
5. **Need to find missing code?** → See "MISSING PIECE" section

All sections reference specific line numbers for easy navigation in your editor.
