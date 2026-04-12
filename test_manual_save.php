<?php
/**
 * Manual test untuk submit yesno jawaban dan verify storage
 */
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\F01Pengisian;
use App\Models\F01Jawaban;
use App\Models\Pertanyaan;

echo "\n=== F01 MANUAL SAVE TEST ===\n\n";

// Get a draft pengisisan
$p = F01Pengisian::where('status', 'draft')->first();

if (!$p) {
    echo "No draft pengisisan found\n";
    exit;
}

echo "Using Pengisian ID: {$p->id}\n\n";

// Get a yesno question in this pengisian's aspeks
$yesnoQuestion = Pertanyaan::where('tipe_input', 'yesno')
    ->where('opsi_jawaban', null)
    ->first();

if (!$yesnoQuestion) {
    echo "No yesno question found\n";
    exit;
}

echo "Using Question ID: {$yesnoQuestion->id} ({$yesnoQuestion->label})\n\n";

// Manually create/update jawaban with capital Y ES
echo "Testing 1: Save with 'Tidak' (capital T)\n";
echo str_repeat("-", 60) . "\n";

F01Jawaban::updateOrCreate(
    [
        'f01_pengisian_id' => $p->id,
        'pertanyaan_id' => $yesnoQuestion->id,
    ],
    [
        'nilai' => 'Tidak',  // Capital Tidak
    ]
);

$saved = F01Jawaban::where('f01_pengisian_id', $p->id)
    ->where('pertanyaan_id', $yesnoQuestion->id)
    ->first();

echo "After saving 'Tidak':\n";
echo "  DB nilai (raw): " . var_export(DB::table('f01_jawaban')
    ->where('f01_pengisian_id', $p->id)
    ->where('pertanyaan_id', $yesnoQuestion->id)
    ->value('nilai'), true) . "\n";
echo "  Model read: " . var_export($saved->nilai, true) . "\n";
echo "  Model type: " . gettype($saved->nilai) . "\n\n";

// Now test with lowercase
echo "Testing 2: Save with 'tidak' (lowercase)\n";
echo str_repeat("-", 60) . "\n";

F01Jawaban::where('f01_pengisian_id', $p->id)
    ->where('pertanyaan_id', $yesnoQuestion->id)
    ->update(['nilai' => json_encode('tidak')]);

$saved = F01Jawaban::where('f01_pengisian_id', $p->id)
    ->where('pertanyaan_id', $yesnoQuestion->id)
    ->first();

echo "After saving 'tidak':\n";
echo "  DB nilai (raw): " . var_export(DB::table('f01_jawaban')
    ->where('f01_pengisian_id', $p->id)
    ->where('pertanyaan_id', $yesnoQuestion->id)
    ->value('nilai'), true) . "\n";
echo "  Model read: " . var_export($saved->nilai, true) . "\n";
echo "  Model type: " . gettype($saved->nilai) . "\n\n";

// Test API response
echo "Testing 3: What API would return\n";
echo str_repeat("-", 60) . "\n";

$allAnswers = F01Jawaban::where('f01_pengisian_id', $p->id)
    ->where('pertanyaan_id', $yesnoQuestion->id)
    ->get(['id', 'nilai']);

foreach ($allAnswers as $a) {
    $apiJson = json_encode(['nilai' => $a->nilai]);
    echo "API response: " . $apiJson . "\n";
    echo "Is equal to 'tidak'? " . ($a->nilai === 'tidak' ? 'YES' : 'NO') . "\n";
}

?>
