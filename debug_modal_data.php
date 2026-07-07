<?php
/**
 * Debug script untuk modal indicator detail
 * Check: periode_id, upp filter, F01 pengisian, F01 indikator nilai
 */

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Http\Kernel')->handle(
    $request = Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// Test parameters
$indikator_id = 1; // Adjust as needed
$periode_id = 1; // Adjust as needed
$upp_id = null; // null = all, or specific UPP ID

echo "\n=== DEBUG MODAL DATA FLOW ===\n";
echo "Indikator ID: {$indikator_id}\n";
echo "Periode ID: {$periode_id}\n";
echo "UPP ID Filter: " . ($upp_id ? $upp_id : 'ALL (no filter)') . "\n";

// 1. Check Indikator
echo "\n--- Step 1: Check Indikator ---\n";
$indikator = DB::table('indikator')
    ->where('id', $indikator_id)
    ->first(['id', 'nama', 'kode', 'aspek_id']);

if ($indikator) {
    echo "✓ Indikator found: {$indikator->nama} (ID: {$indikator->id})\n";
} else {
    echo "✗ Indikator NOT found\n";
    exit;
}

// 2. Check Periode
echo "\n--- Step 2: Check Periode ---\n";
$periode = DB::table('periode')
    ->where('id', $periode_id)
    ->first(['id', 'tahun', 'is_aktif']);

if ($periode) {
    echo "✓ Periode found: {$periode->tahun} (Active: " . ($periode->is_aktif ? 'YES' : 'NO') . ")\n";
} else {
    echo "✗ Periode NOT found\n";
    exit;
}

// 3. Check F02 Skor
echo "\n--- Step 3: Check F02 Skor (Narasi) ---\n";
$f02Skor = DB::table('f02_skors')
    ->where('indikator_id', $indikator_id)
    ->where('periode_id', $periode_id)
    ->first();

if ($f02Skor) {
    echo "✓ F02 Skor found\n";
    for ($i = 0; $i <= 5; $i++) {
        $field = "skor_{$i}";
        $narasi = $f02Skor->{$field} ?? '';
        echo "  - Skor {$i}: " . (strlen($narasi) > 0 ? substr($narasi, 0, 50) . '...' : '(empty)') . "\n";
    }
} else {
    echo "✗ F02 Skor NOT found - this will show empty narasi\n";
}

// 4. Check F01 Pengisian
echo "\n--- Step 4: Check F01 Pengisian (latest, is_latest_version=1) ---\n";

$query = DB::table('f01_pengisian as fp')
    ->where('fp.is_latest_version', 1)
    ->where('fp.periode_id', $periode_id);

if ($upp_id) {
    $query->where('fp.upp_id', $upp_id);
}

$f01Count = $query->whereNull('fp.deleted_at')->count();
echo "Found {$f01Count} F01 Pengisian records\n";

if ($f01Count > 0) {
    $f01Ids = $query->whereNull('fp.deleted_at')->pluck('fp.id')->toArray();
    echo "F01 IDs: " . implode(', ', array_slice($f01Ids, 0, 5)) . (count($f01Ids) > 5 ? "... (+more)" : "") . "\n";

    // 5. Check F01 Indikator Nilai
    echo "\n--- Step 5: Check F01 Indikator Nilai (for this indikator) ---\n";

    $nilaiCount = DB::table('f01_indikator_nilai as fin')
        ->whereIn('fin.f01_pengisian_id', $f01Ids)
        ->where('fin.indikator_id', $indikator_id)
        ->count();

    echo "Found {$nilaiCount} F01 Indikator Nilai records\n";

    if ($nilaiCount > 0) {
        $distribution = DB::table('f01_indikator_nilai as fin')
            ->selectRaw('fin.nilai as skor, COUNT(DISTINCT fin.f01_pengisian_id) as upp_count')
            ->whereIn('fin.f01_pengisian_id', $f01Ids)
            ->where('fin.indikator_id', $indikator_id)
            ->groupBy('fin.nilai')
            ->orderBy('fin.nilai', 'desc')
            ->get();

        echo "Distribution by skor:\n";
        foreach ($distribution as $row) {
            echo "  - Skor {$row->skor}: {$row->upp_count} UPP\n";
        }

        // 6. Check UPP list for each score
        echo "\n--- Step 6: Check UPP List per Score ---\n";
        foreach ($distribution as $row) {
            $skor = $row->skor;
            $uppList = DB::table('f01_indikator_nilai as fin')
                ->join('f01_pengisian as fp', 'fin.f01_pengisian_id', '=', 'fp.id')
                ->join('upps as u', 'fp.upp_id', '=', 'u.id')
                ->selectRaw('u.id as upp_id, u.nama as upp_nama')
                ->whereIn('fin.f01_pengisian_id', $f01Ids)
                ->where('fin.indikator_id', $indikator_id)
                ->where('fin.nilai', $skor)
                ->orderBy('u.nama')
                ->pluck('upp_nama')
                ->toArray();

            echo "  Skor {$skor} UPPs:\n";
            foreach (array_slice($uppList, 0, 3) as $upp) {
                echo "    - {$upp}\n";
            }
            if (count($uppList) > 3) {
                echo "    ... and " . (count($uppList) - 3) . " more\n";
            }
        }

        echo "\n✓ ALL DATA FOUND - Modal should display correctly\n";
    } else {
        echo "✗ NO F01 Indikator Nilai found for this indikator\n";
        echo "  This means no UPP has filled scores for this indikator\n";
    }
} else {
    echo "✗ NO F01 Pengisian records found\n";
    echo "  Check: periode_id={$periode_id}, upp_id=" . ($upp_id ? $upp_id : 'ALL') . "\n";
}

echo "\n=== END DEBUG ===\n\n";
