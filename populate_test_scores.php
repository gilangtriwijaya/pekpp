<?php
/**
 * Populate test data for F01 Indikator Nilai
 * Creates sample scores for indikators to test the modal
 */

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Http\Kernel')->handle(
    $request = Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\DB;

$periode_id = 1; // 2026

// Get all F01 pengisian for this periode
$f01Ids = DB::table('f01_pengisian as fp')
    ->where('fp.is_latest_version', 1)
    ->where('fp.periode_id', $periode_id)
    ->whereNull('fp.deleted_at')
    ->pluck('fp.id')
    ->toArray();

echo "Found " . count($f01Ids) . " F01 Pengisian records\n";

// Get all indikators
$indikators = DB::table('indikator')
    ->whereNull('deleted_at')
    ->pluck('id')
    ->toArray();

echo "Found " . count($indikators) . " Indikators\n";

// Create test data: each F01 gets random scores for each indikator
echo "\nCreating test scores for modal testing...\n";

$count = 0;
foreach ($f01Ids as $f01Id) {
    foreach ($indikators as $indikatorId) {
        // Random score 0-5
        $nilai = rand(0, 5);

        DB::table('f01_indikator_nilai')->insertOrIgnore([
            'f01_pengisian_id' => $f01Id,
            'indikator_id' => $indikatorId,
            'nilai' => $nilai,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $count++;
    }
}

echo "✓ Created {$count} test score records\n";

// Verify
$verifyCount = DB::table('f01_indikator_nilai')->count();
echo "\nVerification: Total f01_indikator_nilai records: {$verifyCount}\n";

// Show distribution for indikator 1
$dist = DB::table('f01_indikator_nilai as fin')
    ->selectRaw('fin.nilai as skor, COUNT(*) as count')
    ->where('fin.indikator_id', 1)
    ->groupBy('fin.nilai')
    ->orderBy('fin.nilai', 'desc')
    ->get();

echo "\nIndikator #1 score distribution:\n";
foreach ($dist as $row) {
    echo "  Skor {$row->skor}: {$row->count} records\n";
}

echo "\n✓ Test data ready! Click modal Detail button to test.\n";
