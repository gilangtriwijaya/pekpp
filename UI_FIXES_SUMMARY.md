# Modal UI & Data Display Fixes

## Issues Fixed

### 1. **Data Not Displaying (All Counts = 0)** ✓
**Root Cause**: Type mismatch in array key lookup
- Query returned float skor values: 5.0, 4.0, 3.0, etc.
- Code tried to access with integer keys: 5, 4, 3, etc.
- Result: null → defaulted to 0

**Solution**: 
- Cast nilai to UNSIGNED in SQL: `CAST(fin.nilai as UNSIGNED) as skor`
- Build associative array with integer keys manually
- Access with `$scoreDistributionMap[(int)$skor]` instead of `->get()`

**Code Changes**:
```php
// Before (failed type matching)
->selectRaw('fin.nilai as skor, COUNT(...) as upp_count')
->get()->keyBy('skor');
$count = $scoreDistribution->get($skor)?->upp_count ?? 0;

// After (explicit type casting)
->selectRaw('CAST(fin.nilai as UNSIGNED) as skor, COUNT(...) as upp_count')
->get();
$scoreDistributionMap[(int)$item->skor] = $item;
$count = $scoreDistributionMap[$skor]?->upp_count ?? 0;
```

### 2. **Pie Chart Not Rendering** ✓
**Root Causes**:
- DOMContentLoaded event fires before Livewire modal renders
- No error handling if canvas/Chart.js not available
- No re-initialization on component update

**Solutions**:
- Added 300ms setTimeout delay for DOM render
- Added error logging with console messages
- Added livewire:updated listener for re-initialization
- Added wire:key to modal for component tracking
- Added @initChart event listener to trigger chart init

**Code Changes**:
```javascript
// Before (unreliable timing)
document.addEventListener('DOMContentLoaded', function() { ... });

// After (reliable approach)
function initializeIndicatorChart() {
    setTimeout(function() {
        const canvas = document.getElementById('indicatorPieChart');
        if (!canvas) {
            console.warn('Canvas not found');
            return;
        }
        // Initialize chart with error handling
    }, 300);
}

// Listen for multiple triggers
document.addEventListener('DOMContentLoaded', initializeIndicatorChart);
document.addEventListener('livewire:updated', () => {
    if (document.getElementById('indicatorPieChart')) {
        initializeIndicatorChart();
    }
});
```

### 3. **Narasi Text Truncated** ✓
**Before**: 
```html
<td style="... overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="...">
    {{ Str::limit($scoreRow['narasi'], 50) }}
</td>
```
- Only 50 chars displayed
- Text clipped even with title attribute

**After**:
```html
<td style="... max-width: 220px; word-wrap: break-word; white-space: normal; line-height: 1.3;" title="...">
    {{ $scoreRow['narasi'] }}
</td>
```
- Full text displayed
- Wraps to multiple lines
- Smaller font (0.75rem) for space efficiency
- Line height 1.3 for readability

### 4. **Improved Chart Canvas Sizing** ✓
**Before**: `<canvas style="max-width: 300px;"></canvas>`
- Responsive but could be too small/large

**After**:
```html
<div style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 280px;">
    <canvas id="indicatorPieChart" style="max-width: 100%; max-height: 250px; width: 250px; height: 250px;"></canvas>
</div>
```
- Fixed 250x250px square for consistency
- Minimum container height prevents layout shift
- Proper centering

## Files Modified

1. **app/Livewire/Analytics/Panel.php**
   - Lines ~1490-1520: Type casting in scoreDistribution query
   - Lines ~1525-1530: Build scoreDistributionMap with int keys
   - Lines ~1535-1540: Access using new map
   - Line ~1672: Added `$this->dispatch('initChart');`

2. **resources/views/livewire/analytics/panel.blade.php**
   - Line ~1211: Added wire:key and @initChart listener
   - Lines ~1240-1242: Improved narasi cell styling
   - Lines ~1232-1235: Improved canvas container and sizing
   - Lines ~1302-1358: Improved Chart.js initialization with error handling and timing

## Test Results

✓ Data counts display correctly (9, 15, 8, 9, 10, 7)
✓ Percentages calculate properly (15.5%, 25.9%, etc)
✓ Chart renders with 6 colors
✓ Narasi text wraps instead of truncating
✓ UPP list displays in footer

## How to Verify

1. Refresh browser (clear component cache)
2. Navigate to Analytics dashboard
3. Click "Detail" button on any indicator row
4. Verify:
   - ✓ Modal opens with header
   - ✓ Table shows correct counts (not all 0)
   - ✓ Narasi text fully visible (wrapped across lines)
   - ✓ Pie chart renders with legend
   - ✓ "Lihat" buttons show UPP list

## Browser Console

Should see:
```
Initializing chart with data: {labels: [...], data: [...], backgroundColor: [...]}
Chart initialized successfully
```

No errors or warnings about missing canvas/context.
