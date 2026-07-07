# Pie Chart Debugging Guide

## Quick Test (Do This First)

1. Open this in browser: `http://your-domain/test-chart.html`
2. You should see a doughnut chart with test data
3. If you see the chart → Chart.js works
4. If you don't see chart → Library issue

## Debug Steps for Modal

### Step 1: Open Browser Developer Tools
- **Chrome/Edge**: Press `F12` or `Ctrl+Shift+I` (Windows) / `Cmd+Shift+I` (Mac)
- **Firefox**: Press `F12`

### Step 2: Go to Console Tab
- Look for any red errors
- Look for `[Chart]` messages

### Step 3: Navigate to Dashboard and Click Detail Button
- Watch the Console in real-time
- You should see messages like:
  ```
  [Chart] Initialization started
  [Chart] Canvas found <canvas#indicatorPieChart>
  [Chart] Canvas context obtained
  [Chart] Data received: {labels: Array(6), data: Array(6), backgroundColor: Array(6)}
  [Chart] Creating new chart instance...
  [Chart] Chart initialized successfully ✓
  ```

## What to Look For

### ✓ Success Signs
- Messages appear in console
- Chart renders in modal
- Data shows: [9, 15, 8, 9, 10, 7]

### ✗ Failure Signs
- `[Chart] Canvas element not found` → Canvas div not in DOM
- `[Chart] Chart.js library not loaded` → Chart.js not loaded
- `[Chart] Error creating chart: ...` → Initialization error
- No `[Chart]` messages → Script not running

## Common Issues & Fixes

### Issue 1: No Chart Messages in Console
**Cause**: Chart initialization script not running
**Fix**:
1. Hard refresh page: `Ctrl+Shift+R` (Windows) or `Cmd+Shift+R` (Mac)
2. Clear browser cache
3. Check if JavaScript errors appear

### Issue 2: "Canvas element not found"
**Cause**: Modal not rendering or canvas ID different
**Check**:
1. Right-click modal → "Inspect Element"
2. Search for `id="indicatorPieChart"`
3. Should see `<canvas id="indicatorPieChart">`

### Issue 3: "Data received: {labels: [], data: [], backgroundColor: []}"
**Cause**: No chart data being passed from backend
**Check**:
1. Are counts displaying in table? (should show 9, 15, 8, etc)
2. If yes → backend data is good, chart issue is UI
3. If no → backend query not working, check logs

### Issue 4: Chart appears but looks small/weird
**Cause**: Canvas sizing issue
**Fix**: Check browser zoom (should be 100%)

## Server-Side Debugging

Check Laravel logs:
```bash
tail -f storage/logs/laravel.log | grep "\[Chart\]\|\[showIndikatorDetail\]\|\[getIndikatorScoreDistribution\]"
```

Check database:
```bash
php check_nilai_data.php
```

## Manual Test

Run this in tinker to verify data:
```bash
php artisan tinker
```

Then paste:
```php
$f01Ids = DB::table('f01_pengisian')
    ->where('is_latest_version', 1)
    ->where('periode_id', 1)
    ->pluck('id')
    ->toArray();

$scoreData = DB::table('f01_indikator_nilai as fin')
    ->selectRaw('CAST(fin.nilai as UNSIGNED) as skor, COUNT(DISTINCT fin.f01_pengisian_id) as upp_count')
    ->whereIn('fin.f01_pengisian_id', $f01Ids)
    ->where('fin.indikator_id', 1)
    ->groupBy('fin.nilai')
    ->orderBy('fin.nilai', 'desc')
    ->get();

dd($scoreData);
exit;
```

Expected output: 6 rows with skor 5,4,3,2,1,0 and upp_count values

## Report These Details

When reporting issue, provide:
1. Screenshot of Console tab (full error messages)
2. Output of `php check_nilai_data.php`
3. Browser & OS version
4. Whether `test-chart.html` shows chart or not
