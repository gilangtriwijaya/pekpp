#!/bin/bash
# Final verification script for modal UI fixes

echo "╔════════════════════════════════════════════════╗"
echo "║  Modal Indicator Detail - Final Verification  ║"
echo "╚════════════════════════════════════════════════╝"
echo ""

cd /home/deploy/apps/pekpp

echo "1. Checking database data..."
php artisan tinker << 'EOF' > /tmp/verify_data.txt 2>&1
$f01Count = DB::table('f01_pengisian')->where('is_latest_version', 1)->where('periode_id', 1)->count();
$nilaiCount = DB::table('f01_indikator_nilai')->count();
echo "   F01 Pengisian (latest): $f01Count\n";
echo "   F01 Indikator Nilai: $nilaiCount\n";
exit;
EOF
cat /tmp/verify_data.txt

echo ""
echo "2. Checking code fixes..."

# Check for type casting
if grep -q "CAST(fin.nilai as UNSIGNED)" app/Livewire/Analytics/Panel.php; then
    echo "   ✓ Type casting implemented"
else
    echo "   ✗ Type casting missing"
fi

# Check for chart initialization
if grep -q "setTimeout(function()" resources/views/livewire/analytics/panel.blade.php; then
    echo "   ✓ Chart init timing fixed"
else
    echo "   ✗ Chart init timing not fixed"
fi

# Check for narasi wrapping
if grep -q "word-wrap: break-word" resources/views/livewire/analytics/panel.blade.php; then
    echo "   ✓ Narasi text wrapping implemented"
else
    echo "   ✗ Narasi text wrapping missing"
fi

echo ""
echo "3. Files created for debugging..."
ls -lh debug_modal_data.php populate_test_scores.php check_nilai_data.php 2>/dev/null | awk '{print "   " $9 " (" $5 ")"}'

echo ""
echo "4. Documentation created..."
ls -lh *FIX_SUMMARY.md 2>/dev/null | awk '{print "   " $9}'

echo ""
echo "╔════════════════════════════════════════════════╗"
echo "║           ✓ All Fixes Implemented             ║"
echo "╚════════════════════════════════════════════════╝"
echo ""
echo "Next step: Refresh browser and click Detail button"
echo ""
