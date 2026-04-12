<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\F03Pengisian;
use Illuminate\Support\Facades\DB;

// Initialize Laravel app
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "\n";
echo str_repeat("=", 100) . "\n";
echo "🗑️  HAPUS F03 RESPONSE DENGAN SKOR SALAH\n";
echo str_repeat("=", 100) . "\n\n";

$responseId = 50;

// Get response details sebelum dihapus
$response = F03Pengisian::find($responseId);

if (!$response) {
    echo "❌ Response ID {$responseId} tidak ditemukan!\n\n";
    exit(1);
}

echo "📋 Data yang akan DIHAPUS:\n";
echo "  Response ID: {$response->id}\n";
echo "  UPP: {$response->upp->nama}\n";
echo "  Periode: {$response->periode->nama}\n";
echo "  Tanggal: {$response->response_date}\n";
echo "  Total Jawaban: {$response->jawaban->count()}\n";
echo "  Rata-rata Skor: " . number_format($response->jawaban()->avg('score'), 2) . "\n";

echo "\n⚠️  ACTION: Menghapus response dan semua jawaban terkaitnya...\n\n";

try {
    DB::beginTransaction();

    // Count jawaban yang akan dihapus
    $jawabanCount = $response->jawaban()->count();

    // Delete response (akan cascade delete semua jawaban)
    $response->delete();

    DB::commit();

    echo "✅ SUCCESS!\n";
    echo "  - Response ID {$responseId} dihapus\n";
    echo "  - {$jawabanCount} jawaban terkait dihapus\n";
    echo "\n";

    // Verify deletion
    $verify = F03Pengisian::find($responseId);
    if (!$verify) {
        echo "✓ Verifikasi: Response sudah tidak ada di database\n\n";
    }

} catch (\Exception $e) {
    DB::rollBack();
    echo "❌ ERROR: {$e->getMessage()}\n\n";
    exit(1);
}

echo str_repeat("=", 100) . "\n";
echo "✅ Selesai\n\n";
