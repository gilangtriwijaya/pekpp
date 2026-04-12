<?php
/**
 * Test API response untuk check apakah nilai dikembalikan
 */
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\F01Pengisian;
use Illuminate\Support\Facades\DB;

echo "\n=== API RESPONSE TEST ===\n\n";

// Get a draft pengisisan dengan jawaban yesno
$pengisians = F01Pengisian::with(['jawaban.pertanyaan', 'periode', 'upp'])
    ->where('status', 'draft')
    ->whereHas('jawaban.pertanyaan', function($q) {
        $q->where('tipe_input', 'yesno');
    })
    ->limit(1)
    ->get();

if ($pengisians->count() === 0) {
    echo "No draft pengisians with yesno answers found\n";
    exit;
}

$p = $pengisians->first();

echo "Testing Pengisian ID: {$p->id}\n";
echo "UPP: {$p->upp->nama}\n";
echo "\n";

// Simulate what API does
$jawaban = $p->jawaban()
    ->with('pertanyaan')
    ->get()
    ->keyBy('pertanyaan_id');

echo "Total jawaban in DB: " . $jawaban->count() . "\n";
echo "Yesno jawaban:\n";
echo str_repeat("-", 80) . "\n";

$yesnos = $jawaban->filter(function($j) {
    return $j->pertanyaan->tipe_input === 'yesno';
});

echo "Found " . $yesnos->count() . " yesno answers\n\n";

foreach ($yesnos->take(5) as $j) {
    $answer = $jawaban->get($j->pertanyaan_id);
    
    echo "Q{$j->pertanyaan_id}:\n";
    echo "  DB nilai (raw): " . var_export($j->nilai, true) . "\n";
    echo "  DB nilai (type): " . gettype($j->nilai) . "\n";
    echo "  Model casted nilai: " . var_export($answer->nilai, true) . "\n";
    echo "  Model casted type: " . gettype($answer->nilai) . "\n";
    
    // What API would return
    $apiResponse = [
        'id' => $j->pertanyaan_id,
        'nilai' => $answer->nilai,
        'answered' => !empty($answer->nilai)
    ];
    
    echo "  API response: " . json_encode($apiResponse) . "\n\n";
}

echo "\n=== CHECKING API CASTING ===\n";
echo "Model has: protected \$casts = ['nilai' => 'json'];\n";
echo "So nilai should be auto-cast from JSON string to PHP value\n";
echo "Then when returned via API, Laravel re-encodes to JSON\n\n";

// Check if there's any null issue
echo "Checking for NULL issues:\n";
$nullAnswers = F01Jawaban::whereIn('pertanyaan_id', 
    DB::table('pertanyaan')->where('tipe_input', 'yesno')->pluck('id')
)->where('nilai', null)->count();

echo "Yesno questions with NULL nilai: " . $nullAnswers . "\n";

?>
