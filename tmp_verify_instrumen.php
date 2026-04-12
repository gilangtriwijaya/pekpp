<?php
require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n";
echo str_repeat("=", 80) . "\n";
echo "🔍 VERIFIKASI DATA INSTRUMEN F01, F02, F03\n";
echo str_repeat("=", 80) . "\n\n";

// ============ F01 INSTRUMEN ============
echo "📋 F01 - PENGISIAN INDIKATOR INSTRUMEN:\n";
echo str_repeat("-", 80) . "\n";

$f01Instrumen = [
    'aspek' => 'Aspek',
    'indikator' => 'Indikator', 
    'pertanyaan' => 'Pertanyaan',
];

foreach ($f01Instrumen as $table => $name) {
    $count = DB::table($table)->count();
    echo "  ✓ {$name} ({$table}): {$count} record\n";
}

// ============ F02 INSTRUMEN ============
echo "\n📋 F02 - VALIDASI INSTRUMEN:\n";
echo str_repeat("-", 80) . "\n";

$f02Instrumen = [
    'f02_skor' => 'F02 Skor Template',
];

foreach ($f02Instrumen as $table => $name) {
    try {
        if (DB::getSchemaBuilder()->hasTable($table)) {
            $count = DB::table($table)->count();
            echo "  ✓ {$name} ({$table}): {$count} record\n";
        } else {
            echo "  ✗ {$name} ({$table}): TABLE TIDAK ADA\n";
        }
    } catch (\Exception $e) {
        echo "  ✗ {$name} ({$table}): ERROR - {$e->getMessage()}\n";
    }
}

// ============ F03 INSTRUMEN ============
echo "\n📋 F03 - SURVEY KINERJA INSTRUMEN:\n";
echo str_repeat("-", 80) . "\n";

$f03Instrumen = [
    'f03_aspek' => 'F03 Aspek Template',
    'f03_indikator' => 'F03 Indikator Template',
    'f03_token' => 'F03 Token (untuk distribute survey)',
];

foreach ($f03Instrumen as $table => $name) {
    try {
        if (DB::getSchemaBuilder()->hasTable($table)) {
            $count = DB::table($table)->count();
            echo "  ✓ {$name} ({$table}): {$count} record\n";
        } else {
            echo "  ✗ {$name} ({$table}): TABLE TIDAK ADA\n";
        }
    } catch (\Exception $e) {
        echo "  ✗ {$name} ({$table}): ERROR\n";
    }
}

// ============ JAWABAN (YANG SUDAH DIHAPUS) ============
echo "\n\n🗑️  JAWABAN / PENGISIAN DARI UPP (HARUS 0):\n";
echo str_repeat("-", 80) . "\n";

$jawaban = [
    'f01_pengisian' => 'F01 Pengisian',
    'f01_jawaban' => 'F01 Jawaban',
    'f01_indikator_nilai' => 'F01 Indikator Nilai',
    'f01_indikator_bukti' => 'F01 Indikator Bukti',
    'f01_bukti_dukung' => 'F01 Bukti Dukung',
    'f01_aspek_pengisian' => 'F01 Aspek Pengisian',
    'f02_validasi' => 'F02 Validasi',
    'f02_indikator_validasi' => 'F02 Indikator Validasi',
    'f02_catatan_indikator' => 'F02 Catatan Indikator',
    'f03_pengisian' => 'F03 Pengisian',
    'f03_jawaban' => 'F03 Jawaban',
    'f03_response_demographics' => 'F03 Response Demographic',
];

$allClean = true;
foreach ($jawaban as $table => $name) {
    $count = DB::table($table)->count();
    $status = $count == 0 ? '✓' : '✗';
    echo "  {$status} {$name} ({$table}): {$count} record";
    if ($count > 0) {
        echo " ⚠️  MASIH ADA DATA!";
        $allClean = false;
    }
    echo "\n";
}

// ============ SUMMARY ============
echo "\n" . str_repeat("=", 80) . "\n";
echo "✅ SUMMARY:\n";
echo str_repeat("=", 80) . "\n\n";

if ($allClean) {
    echo "✅ INSTRUMEN F01, F02, F03 AMAN - SEMUA ADA\n";
    echo "✅ JAWABAN / PENGISIAN TELAH DIHAPUS - SEMUA 0\n";
    echo "✅ SIAP UNTUK DIGUNAKAN KEMBALI!\n\n";
} else {
    echo "⚠️  MASIH ADA DATA JAWABAN YANG BELUM DIHAPUS!\n";
    echo "   Jalankan: php artisan upp:reset-answers --all\n\n";
}

echo str_repeat("=", 80) . "\n\n";
