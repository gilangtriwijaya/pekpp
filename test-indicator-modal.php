<?php

// Test script to verify indicator detail modal data generation

require_once __DIR__ . '/bootstrap/app.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// Pick a random indikator
$indikator = DB::table('indikator')->first();

if (!$indikator) {
    echo "❌ No indikator found\n";
    exit(1);
}

echo "Testing Indikator: {$indikator->id} - {$indikator->nama}\n";

// Get active periode
$periode = DB::table('periode')->where('is_aktif', 1)->orderByDesc('tahun')->first();

if (!$periode) {
    echo "❌ No active periode found\n";
    exit(1);
}

echo "Using Periode: {$periode->id} - {$periode->tahun}\n\n";

// Simulate filter scope (all UPPs if not filtered)
$scopedUppIds = DB::table('upps')->where('aktif', 1)->pluck('id')->toArray();
echo "Scoped UPP IDs: " . count($scopedUppIds) . " total\n\n";

// Get F01 pengisian count
$f01Count = DB::table('f01_pengisian as fp')
    ->where('fp.is_latest_version', 1)
    ->where('fp.periode_id', $periode->id)
    ->when(!empty($scopedUppIds), function($q) use ($scopedUppIds) {
        $q->whereIn('fp.upp_id', $scopedUppIds);
    })
    ->whereNull('fp.deleted_at')
    ->count();

echo "F01 pengisian (latest version): {$f01Count}\n\n";

// Get score distribution like the backend does
$scoreColors = [
    0 => '#A32D2D', // Red
    1 => '#C43E3E', // Dark Red
    2 => '#D97706', // Orange
    3 => '#EAB308', // Amber
    4 => '#10B981', // Green
    5 => '#185FA5'  // Blue
];

$scorePredikat = [
    0 => 'Prioritas Pembinaan',
    1 => 'Cukup Dengan Catatan',
    2 => 'Cukup',
    3 => 'Baik Dengan Catatan',
    4 => 'Baik',
    5 => 'Istimewa'
];

// Get F02 Skor narasi
$f02Skor = DB::table('f02_skors')
    ->where('indikator_id', $indikator->id)
    ->where('periode_id', $periode->id)
    ->first();

echo "F02 Skor found: " . ($f02Skor ? 'YES' : 'NO') . "\n";

if (!$f02Skor) {
    echo "⚠️  Creating test F02 Skor record...\n";
    DB::table('f02_skors')->insert([
        'indikator_id' => $indikator->id,
        'periode_id' => $periode->id,
        'skor_0' => 'Skor 0 - Prioritas Pembinaan',
        'skor_1' => 'Skor 1 - Cukup Dengan Catatan',
        'skor_2' => 'Skor 2 - Cukup',
        'skor_3' => 'Skor 3 - Baik Dengan Catatan',
        'skor_4' => 'Skor 4 - Baik',
        'skor_5' => 'Skor 5 - Istimewa',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $f02Skor = DB::table('f02_skors')
        ->where('indikator_id', $indikator->id)
        ->where('periode_id', $periode->id)
        ->first();

    echo "✓ Created\n\n";
}

// Get F01 pengisian IDs
$f01PengisianIds = DB::table('f01_pengisian as fp')
    ->where('fp.is_latest_version', 1)
    ->where('fp.periode_id', $periode->id)
    ->when(!empty($scopedUppIds), function($q) use ($scopedUppIds) {
        $q->whereIn('fp.upp_id', $scopedUppIds);
    })
    ->whereNull('fp.deleted_at')
    ->pluck('fp.id')
    ->toArray();

echo "F01 Pengisian IDs to query: " . count($f01PengisianIds) . "\n\n";

if (count($f01PengisianIds) === 0) {
    echo "❌ No F01 pengisian found - cannot build chart data\n";
    exit(1);
}

// Get score distribution
$scoreDistribution = DB::table('f01_indikator_nilai as fin')
    ->selectRaw('CAST(fin.nilai as UNSIGNED) as skor, COUNT(DISTINCT fin.f01_pengisian_id) as upp_count')
    ->whereIn('fin.f01_pengisian_id', $f01PengisianIds)
    ->where('fin.indikator_id', $indikator->id)
    ->groupBy('fin.nilai')
    ->orderBy('fin.nilai', 'desc')
    ->get();

echo "Score Distribution Query Result:\n";
foreach ($scoreDistribution as $item) {
    echo "  Skor {$item->skor}: {$item->upp_count} UPP\n";
}

// Create map
$scoreDistributionMap = [];
foreach ($scoreDistribution as $item) {
    $scoreDistributionMap[(int)$item->skor] = $item;
}

// Calculate total UPP
$totalUpp = (int) DB::table('f01_pengisian as fp')
    ->where('fp.is_latest_version', 1)
    ->where('fp.periode_id', $periode->id)
    ->when(!empty($scopedUppIds), function($q) use ($scopedUppIds) {
        $q->whereIn('fp.upp_id', $scopedUppIds);
    })
    ->whereNull('fp.deleted_at')
    ->count();

echo "\nTotal UPP: {$totalUpp}\n";

// Build chart data
$chartLabels = [];
$chartData = [];
$chartColors = [];
$scores = [];

echo "\n--- Building Chart Data ---\n";

foreach ([5, 4, 3, 2, 1, 0] as $skor) {
    $count = $scoreDistributionMap[$skor]?->upp_count ?? 0;
    $percentage = $totalUpp > 0 ? ($count / $totalUpp) * 100 : 0;

    $narasiField = 'skor_' . $skor;
    $narasi = $f02Skor?->{$narasiField} ?? '';

    echo "\nSkor {$skor} ({$scorePredikat[$skor]}):\n";
    echo "  Count: {$count}\n";
    echo "  Percentage: " . round($percentage, 1) . "%\n";
    echo "  Narasi: " . substr($narasi, 0, 50) . "...\n";
    echo "  Color: {$scoreColors[$skor]}\n";

    if ($count > 0) {
        $chartLabels[] = $scorePredikat[$skor];
        $chartData[] = $count;
        $chartColors[] = $scoreColors[$skor];
    }
}

echo "\n--- Chart JSON ---\n";
echo json_encode([
    'labels' => $chartLabels,
    'data' => $chartData,
    'backgroundColor' => $chartColors
], JSON_PRETTY_PRINT) . "\n";

echo "\n✓ Test Complete\n";
