<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Opd;
use App\Models\OpdUnit;
use App\Models\Upp;
use Illuminate\Support\Str;

// Initialize Laravel app
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "CREATING UPP RECORDS FOR MISSING UNITS\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// The 3 missing units
$missingUnitsSsoIds = [24, 25, 26];

foreach ($missingUnitsSsoIds as $ssoId) {
    $unit = OpdUnit::where('sso_id', $ssoId)->first();
    $opd = Opd::find($unit->opd_id);
    
    echo "Processing: " . $unit->nama . "\n";
    echo "  sso_id: " . $unit->sso_id . "\n";
    echo "  Parent OPD: " . $opd->nama . " (sso_id: " . $opd->sso_id . ")\n";
    
    // Find parent UPP (the OPD's UPP)
    $parentUpp = Upp::where('opd_id_sso', $opd->sso_id)
                    ->whereNull('unit_opd_id_sso')
                    ->first();
    
    if (!$parentUpp) {
        echo "  ❌ ERROR: Could not find parent UPP for OPD!\n\n";
        continue;
    }
    
    echo "  Parent UPP: " . $parentUpp->nama . " (ID: " . $parentUpp->id . ")\n";
    
    // Check if UPP already exists for this unit
    $existingUpp = Upp::where('unit_opd_id_sso', $ssoId)->first();
    if ($existingUpp) {
        echo "  ⚠️  UPP already exists: " . $existingUpp->nama . " (ID: " . $existingUpp->id . ")\n\n";
        continue;
    }
    
    // Generate kode
    $baseKode = Str::slug($unit->nama);
    $kode = substr($baseKode, 0, 50); // Limit to 50 chars
    
    // Make kode unique by checking
    $counter = 1;
    $originalKode = $kode;
    while (Upp::where('kode', $kode)->exists()) {
        $kode = $originalKode . '-' . $counter;
        $kode = substr($kode, 0, 50);
        $counter++;
    }
    
    echo "  Generated kode: " . $kode . "\n";
    
    // Create the UPP
    $newUpp = Upp::create([
        'kode' => $kode,
        'nama' => $unit->nama,
        'jenis' => 'unit',
        'parent_upp_id' => $parentUpp->id,
        'opd_id_sso' => $opd->sso_id,
        'unit_opd_id_sso' => $unit->sso_id,
        'aktif' => 1
    ]);
    
    echo "  ✅ Created UPP ID: " . $newUpp->id . "\n\n";
}

echo "───────────────────────────────────────────────────────────────\n";
echo "✅ PROCESS COMPLETED\n";
echo "───────────────────────────────────────────────────────────────\n\n";

// Verify the new data
echo "📊 VERIFICATION:\n\n";

$allOpdUnits = OpdUnit::pluck('sso_id')->filter()->all();
$opdUnitsInUpps = Upp::pluck('unit_opd_id_sso')->filter()->unique()->all();

echo "  OPD Units with sso_id: " . count($allOpdUnits) . "\n";
echo "  OPD Units referenced in UPPs: " . count($opdUnitsInUpps) . "\n";
echo "  Coverage: " . number_format((count($opdUnitsInUpps) / count($allOpdUnits)) * 100, 1) . "%\n\n";

$missingOpdUnits = array_diff($allOpdUnits, $opdUnitsInUpps);
if (empty($missingOpdUnits)) {
    echo "  ✅ ALL OPD UNITS ARE NOW REFERENCED IN UPPs!\n\n";
} else {
    echo "  ⚠️  Still missing: " . count($missingOpdUnits) . " units\n\n";
}

echo "═══════════════════════════════════════════════════════════════\n\n";
