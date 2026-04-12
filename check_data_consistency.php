<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Opd;
use App\Models\OpdUnit;
use App\Models\Upp;
use Illuminate\Database\Capsule\Manager as DB;

// Initialize Laravel app
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "DATA CONSISTENCY CHECK: OPD, OpdUnit, and UPP Tables\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Get counts
$opdCount = Opd::count();
$opdUnitCount = OpdUnit::count();
$uppCount = Upp::count();

echo "📊 DATA COUNTS:\n";
echo "  • OPDs: " . $opdCount . "\n";
echo "  • OPD Units: " . $opdUnitCount . "\n";
echo "  • UPPs: " . $uppCount . "\n\n";

// Check 1: OPDs referenced in UPPs
echo "───────────────────────────────────────────────────────────────\n";
echo "✓ CHECK 1: OPDs in UPPs\n";
echo "───────────────────────────────────────────────────────────────\n";

$allOpds = Opd::pluck('sso_id')->filter()->all();
$opdsInUpps = Upp::pluck('opd_id_sso')->filter()->unique()->all();

echo "  OPDs with sso_id: " . count($allOpds) . "\n";
echo "  OPDs referenced in UPPs: " . count($opdsInUpps) . "\n\n";

$missingOpds = array_diff($allOpds, $opdsInUpps);
if (!empty($missingOpds)) {
    echo "  ⚠️  OPDs NOT referenced in UPPs (sso_id): \n";
    foreach ($missingOpds as $ssoId) {
        $opd = Opd::where('sso_id', $ssoId)->first();
        if ($opd) {
            echo "     - ID {$ssoId}: {$opd->nama}\n";
        }
    }
    echo "\n";
} else {
    echo "  ✅ All OPDs are referenced in UPPs\n\n";
}

// Check 2: OPD Units referenced in UPPs
echo "───────────────────────────────────────────────────────────────\n";
echo "✓ CHECK 2: OPD Units in UPPs\n";
echo "───────────────────────────────────────────────────────────────\n";

$allOpdUnits = OpdUnit::pluck('sso_id')->filter()->all();
$opdUnitsInUpps = Upp::pluck('unit_opd_id_sso')->filter()->unique()->all();

echo "  OPD Units with sso_id: " . count($allOpdUnits) . "\n";
echo "  OPD Units referenced in UPPs: " . count($opdUnitsInUpps) . "\n\n";

$missingOpdUnits = array_diff($allOpdUnits, $opdUnitsInUpps);
if (!empty($missingOpdUnits)) {
    echo "  ⚠️  OPD Units NOT referenced in UPPs (sso_id): \n";
    foreach ($missingOpdUnits as $ssoId) {
        $unit = OpdUnit::where('sso_id', $ssoId)->first();
        if ($unit) {
            $opd = Opd::find($unit->opd_id);
            $opdName = $opd ? $opd->nama : "Unknown OPD";
            echo "     - ID {$ssoId}: {$unit->nama} (OPD: {$opdName})\n";
        }
    }
    echo "\n";
} else {
    echo "  ✅ All OPD Units are referenced in UPPs\n\n";
}

// Check 3: UPPs referencing non-existent OPDs
echo "───────────────────────────────────────────────────────────────\n";
echo "✓ CHECK 3: UPPs with Invalid OPD References\n";
echo "───────────────────────────────────────────────────────────────\n";

$invaliddOpdUppCount = Upp::whereNotNull('opd_id_sso')->where(function ($q) {
    $q->whereNotIn('opd_id_sso', Opd::pluck('sso_id')->toArray());
})->count();

if ($invaliddOpdUppCount > 0) {
    echo "  ⚠️  Found " . $invaliddOpdUppCount . " UPPs with invalid OPD references:\n";
    $invalidOpdUpps = Upp::whereNotNull('opd_id_sso')->where(function ($q) {
        $q->whereNotIn('opd_id_sso', Opd::pluck('sso_id')->toArray());
    })->get();
    foreach ($invalidOpdUpps as $upp) {
        echo "     - UPP ID {$upp->id}: {$upp->nama} (opd_id_sso: {$upp->opd_id_sso})\n";
    }
    echo "\n";
} else {
    echo "  ✅ All UPPs have valid OPD references\n\n";
}

// Check 4: UPPs referencing non-existent OPD Units
echo "───────────────────────────────────────────────────────────────\n";
echo "✓ CHECK 4: UPPs with Invalid OPD Unit References\n";
echo "───────────────────────────────────────────────────────────────\n";

$invalidOpdUnitUppCount = Upp::whereNotNull('unit_opd_id_sso')->where(function ($q) {
    $q->whereNotIn('unit_opd_id_sso', OpdUnit::pluck('sso_id')->toArray());
})->count();

if ($invalidOpdUnitUppCount > 0) {
    echo "  ⚠️  Found " . $invalidOpdUnitUppCount . " UPPs with invalid OPD Unit references:\n";
    $invalidOpdUnitUpps = Upp::whereNotNull('unit_opd_id_sso')->where(function ($q) {
        $q->whereNotIn('unit_opd_id_sso', OpdUnit::pluck('sso_id')->toArray());
    })->get();
    foreach ($invalidOpdUnitUpps as $upp) {
        echo "     - UPP ID {$upp->id}: {$upp->nama} (unit_opd_id_sso: {$upp->unit_opd_id_sso})\n";
    }
    echo "\n";
} else {
    echo "  ✅ All UPPs have valid OPD Unit references\n\n";
}

// Summary
echo "───────────────────────────────────────────────────────────────\n";
echo "📋 SUMMARY\n";
echo "───────────────────────────────────────────────────────────────\n";

$totalIssues = count($missingOpds) + count($missingOpdUnits) + $invaliddOpdUppCount + $invalidOpdUnitUppCount;

if ($totalIssues === 0) {
    echo "✅ All data from OPD and OPD Unit tables are properly referenced in UPPs!\n";
    echo "   Data consistency check PASSED.\n";
} else {
    echo "⚠️  Found " . $totalIssues . " inconsistencies:\n";
    echo "   - Missing OPD references in UPPs: " . count($missingOpds) . "\n";
    echo "   - Missing OPD Unit references in UPPs: " . count($missingOpdUnits) . "\n";
    echo "   - Invalid OPD references in UPPs: " . $invaliddOpdUppCount . "\n";
    echo "   - Invalid OPD Unit references in UPPs: " . $invalidOpdUnitUppCount . "\n\n";
    echo "   Please review and fix these inconsistencies.\n";
}

echo "\n═══════════════════════════════════════════════════════════════\n\n";
