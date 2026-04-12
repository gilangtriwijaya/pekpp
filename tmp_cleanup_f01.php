<?php
require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n";
echo "🔍 Data f01_pengisian yang tersisa:\n";
echo str_repeat("=", 80) . "\n\n";

$data = DB::table('f01_pengisian')->get();

foreach ($data as $row) {
    echo "ID: {$row->id}\n";
    echo "UPP ID: {$row->upp_id}\n";
    echo "Periode ID: {$row->periode_id}\n";
    echo "Status: {$row->status}\n";
    echo "Created At: {$row->created_at}\n";
    echo "Deleted At: {$row->deleted_at}\n";
    echo "\n";
}

if (count($data) == 0) {
    echo "✅ Tidak ada data f01_pengisian\n\n";
} else {
    echo "\n🗑️  Hapus data f01_pengisian secara paksa...\n";
    DB::statement('SET FOREIGN_KEY_CHECKS=0');
    DB::table('f01_pengisian')->delete();
    DB::statement('SET FOREIGN_KEY_CHECKS=1');
    echo "✅ Data dihapus!\n\n";
}
