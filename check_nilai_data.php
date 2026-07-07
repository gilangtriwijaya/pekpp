<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Http\Kernel')->handle(
    $request = Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\DB;

echo "=== F01 INDIKATOR NILAI DATA CHECK ===\n\n";

// Get all F01 pengisian for periode 1
$f01Ids = DB::table('f01_pengisian')
    ->where('is_latest_version', 1)
    ->where('periode_id', 1)
    ->whereNull('deleted_at')
    ->pluck('id')
    ->toArray();

echo "F01 Pengisian IDs (periode 1, latest): " . count($f01Ids) . "\n";
if (count($f01Ids) > 0) {
    echo "Sample IDs: " . implode(', ', array_slice($f01Ids, 0, 5)) . "\n\n";

    // Check if any indikator_nilai records exist for these f01 IDs
    $nilaiBefore = DB::table('f01_indikator_nilai')
        ->whereIn('f01_pengisian_id', $f01Ids)
        ->count();

    echo "Total f01_indikator_nilai records for these F01 IDs: {$nilaiBefore}\n\n";

    // Check distribution for indikator 1
    $dist = DB::table('f01_indikator_nilai as fin')
        ->selectRaw('fin.nilai as skor, COUNT(DISTINCT fin.f01_pengisian_id) as upp_count')
        ->whereIn('fin.f01_pengisian_id', $f01Ids)
        ->where('fin.indikator_id', 1)
        ->groupBy('fin.nilai')
        ->orderBy('fin.nilai', 'desc')
        ->get();

    echo "Distribution for Indikator #1:\n";
    foreach ($dist as $row) {
        echo "  Skor {$row->skor}: {$row->upp_count} UPP\n";
    }

    if ($dist->count() === 0) {
        echo "\n⚠️  No records found for indikator 1\n";
        echo "Checking raw data...\n";

        // Check if ANY records exist at all
        $anyRecords = DB::table('f01_indikator_nilai')
            ->whereIn('f01_pengisian_id', $f01Ids)
            ->limit(5)
            ->get(['f01_pengisian_id', 'indikator_id', 'nilai']);

        if ($anyRecords->count() > 0) {
            echo "Sample records for these F01 IDs:\n";
            foreach ($anyRecords as $row) {
                echo "  F01: {$row->f01_pengisian_id}, Indikator: {$row->indikator_id}, Nilai: {$row->nilai}\n";
            }
        }
    }
}

echo "\n=== END ===\n";
