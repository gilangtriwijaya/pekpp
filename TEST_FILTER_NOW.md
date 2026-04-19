# 🧪 Testing Filter - Step by Step

## Current Implementation

I've made these changes:
1. ✅ Added global `handleSubmitUppFilter()` function in JavaScript
2. ✅ Added `onclick="handleSubmitUppFilter(event)"` to submit button  
3. ✅ Button now calls `Livewire.dispatch('setUppFilter', { upp_id: uppId })`
4. ✅ PHP handler `setUppFilter()` receives event and loads filtered data
5. ✅ `livewire:updated` listener re-initializes charts

## How to Test NOW

### 1. Open Analytics Page
```
http://localhost:8000/analytics
```

### 2. Open Browser Console (F12)
- Press `F12` → Click "Console" tab
- You should see initial logs showing charts initialized with all data

### 3. Click "Filter UPP" Button
- Look in Console for: `🎯 handleSubmitUppFilter() FIRED`
- If NOT there, the button click isn't working

### 4. Select ONE UPP
- Check one checkbox in modal
- Make sure it's checked (blue checkbox)

### 5. Click "Tampilkan Data" Button  
- **Watch the Console closely**
- Should see sequence:
```
🎯 handleSubmitUppFilter() FIRED - onclick attribute triggered
✓ Selected checkboxes: 1
✓ Selected UPP ID: [number]
🔄 >>> CALLING filterByUpp with upp_id: [number]
✓ Livewire.dispatch setUppFilter called
```

### 6. Check for Server Response
Then after a moment:
```
═══════════════════════════════════════════════════════
🚀 setUppFilter EVENT RECEIVED
═══════════════════════════════════════════════════════
```

Then:
```
═══════════════════════════════════════════════════════
🔄 LIVEWIRE:UPDATED EVENT FIRED
═══════════════════════════════════════════════════════
📊 Current window.chartDataFromServer state:
   - upp_id: [number]
   - f02_data length: 1
   - f03_data length: 1
   - ipp_data length: 1
🔄 Re-initializing all charts...
   ✓ F02 chart re-initialized
   ✓ F03 chart re-initialized
   ✓ IPP chart re-initialized
   ✓ Aspek chart re-initialized
✅ All charts updated successfully!
```

### 7. Check Charts on Page
- F02 should show **1** row (was 38)
- F03 should show **1** row (was 45)
- IPP should show **1** row (was 38)
- Badge should say "Filter by UPP"

## If It Doesn't Work

**Check 1: Did console show handleSubmitUppFilter() FIRED?**
- If NO → Button click didn't fire. Check onclick attribute in button.
- If YES → Continue

**Check 2: Did console show setUppFilter EVENT RECEIVED?**
- If NO → Livewire.dispatch didn't work or event didn't reach PHP
- If YES → Continue  

**Check 3: Did console show LIVEWIRE:UPDATED EVENT FIRED?**
- If NO → Component didn't re-render
- If YES → Charts should have updated

## Server Logs

Also check server logs:
```bash
tail -50 storage/logs/laravel.log | grep -E "handleSubmitUppFilter|setUppFilter|🚀 setUppFilter"
```

Should show:
```
🚀 setUppFilter EVENT RECEIVED
Event parameter upp_id: [number]
✓ $this->upp_id set to: [number]
✓ updateFilters() completed. Chart data now:
   f02_data count: 1
   f03_data count: 1
   ipp_data count: 1
```

## Most Likely Issues

1. **Button click not firing** → Check if onclick attribute is in the HTML
2. **Event not reaching PHP** → Check Livewire.dispatch syntax
3. **Charts not updating** → Check if `livewire:updated` event fires
4. **Data shows 38 instead of 1** → Filter not applied in query

---

**PLEASE TEST NOW and paste the console output here!**

I need to see the exact console logs to diagnose further.
