<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\F01Pengisian;

// Get recent pengisians dengan jawaban
$pengisians = F01Pengisian::with(['jawaban.pertanyaan', 'periode', 'upp'])
    ->where('status', 'draft')
    ->orderBy('id', 'desc')
    ->limit(2)
    ->get();

if ($pengisians->count() === 0) {
    echo "No draft pengisians found\n";
    exit;
}

$p = $pengisians->first();

echo "\n=== API RESPONSE DEBUG ===\n";
echo "Pengisian ID: {$p->id}\n";
echo "Total Jawaban: " . $p->jawaban->count() . "\n\n";

echo "Sample Jawaban (raw from DB):\n";
echo str_repeat("-", 80) . "\n";

foreach ($p->jawaban->take(5) as $j) {
    $tipe = $j->pertanyaan->tipe_input;
    $nilaiRaw = $j->nilai; // Raw from DB
    $nilaiBinary = mb_detect_encoding($j->nilai);
    
    echo "Q{$j->pertanyaan_id} (tipe: {$tipe})\n";
    echo "  Raw nilai: " . var_export($nilaiBinary, true) . "\n";
    echo "  Nilai content: ";
    
    if (is_string($nilaiBinary)) {
        var_dump($j->nilai);
    } else {
        var_dump($nilaiBinary);
    }
    echo "\n";
}

echo "\n=== WHAT API WOULD RETURN ===\n";
echo "If nilai cast as 'json' in model, API returns:\n";
echo json_encode($p->jawaban->first()->nilai) . "\n";

echo "\nIf nilai NOT cast as 'json', API returns:\n";
echo json_encode($p->jawaban->first()->nilai) . "\n";
?>
