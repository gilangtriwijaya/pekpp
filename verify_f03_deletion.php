<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Upp;
use App\Models\F03Pengisian;
use Illuminate\Support\Facades\DB;

// Initialize Laravel app
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "\n";
echo str_repeat("=", 100) . "\n";
echo "✅ VERIFIKASI SETELAH PENGHAPUSAN F03 RESPONSE\n";
echo str_repeat("=", 100) . "\n\n";

// Find Dinas Pendidikan Pemuda dan Olahraga
$dinas = Upp::where('nama', 'Dinas Pendidikan Pemuda dan Olahraga')->first();

if (!$dinas) {
    echo "❌ Dinas tidak ditemukan\n\n";
    exit;
}

echo "🏢 DINAS: {$dinas->nama} (ID: {$dinas->id})\n\n";

// Get all F03 responses
$responses = F03Pengisian::where('upp_id', $dinas->id)
    ->with(['periode', 'jawaban'])
    ->orderBy('response_date', 'desc')
    ->get();

echo "📊 Total F03 Responses: {$responses->count()}\n";
echo "   (Sebelumnya: 11 responses)\n\n";

// Check for any response with all skor 1
$badResponses = $responses->filter(function($resp) {
    $avgScore = $resp->jawaban()->avg('score');
    return $avgScore <= 1.5; // Sangat rendah
});

if ($badResponses->isEmpty()) {
    echo "✅ BAGUS! Tidak ada response dengan skor sangat rendah (semua skor 1)\n";
    echo "   Response ID 50 sudah berhasil dihapus.\n\n";
} else {
    echo "⚠️ Masih ada response dengan skor rendah:\n";
    foreach ($badResponses as $resp) {
        echo "   - Response ID: {$resp->id}, Rata-rata Skor: " . number_format($resp->jawaban()->avg('score'), 2) . "\n";
    }
}

// Show summary of current responses
echo "\n📋 SUMMARY SEMUA RESPONSES SEKARANG:\n";
echo str_repeat("-", 100) . "\n";

foreach ($responses as $resp) {
    $avgScore = $resp->jawaban()->avg('score');
    echo sprintf(
        "ID: %-3d | %s | Skor: %.2f | %d jawaban\n",
        $resp->id,
        $resp->response_date->format('Y-m-d H:i'),
        $avgScore,
        $resp->jawaban->count()
    );
}

echo "\n" . str_repeat("=", 100) . "\n";
echo "✅ Verifikasi selesai\n\n";
