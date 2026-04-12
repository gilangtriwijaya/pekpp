<?php
require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = [
    'upps' => 'UPP',
    'f01_pengisian' => 'F01 Pengisian',
    'f01_jawaban' => 'F01 Jawaban',
    'f02_validasi' => 'F02 Validasi',
    'f03_pengisian' => 'F03 Pengisian',
    'f03_jawaban' => 'F03 Jawaban',
    'aspek' => 'Aspek',
    'indikator' => 'Indikator',
    'pertanyaan' => 'Pertanyaan'
];

echo "\n📊 DATA SNAPSHOT SEBELUM RESET:\n";
echo str_repeat('=', 50) . "\n";

foreach ($tables as $table => $name) {
    try {
        $count = DB::table($table)->count();
        echo sprintf("%-20s | %5d record\n", $name, $count);
    } catch (\Exception $e) {
        echo sprintf("%-20s | ERROR\n", $name);
    }
}

echo str_repeat('=', 50) . "\n";
echo "✅ Data snapshot complete.\n\n";
