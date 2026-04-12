<?php
/**
 * Normalize yesno answers in database from capital to lowercase
 * Fixes issue where existing data has "Ya"/"Tidak" but frontend expects "ya"/"tidak"
 */

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\F01Jawaban;

echo "\n=== F01 JAWABAN NORMALIZATION ===\n\n";

// Find yesno type questions
$yesnoQuestions = DB::table('pertanyaan')
    ->where('tipe_input', 'yesno')
    ->where('opsi_jawaban', null)
    ->pluck('id');

echo "Found " . $yesnoQuestions->count() . " yesno type questions\n";

// Find jawaban dengan capital Ya/Tidak
$capitalAnswers = F01Jawaban::whereIn('pertanyaan_id', $yesnoQuestions)
    ->where(function($q) {
        $q->whereRaw("JSON_UNQUOTE(nilai) = 'Ya'")
          ->orWhereRaw("JSON_UNQUOTE(nilai) = 'Tidak'");
    })
    ->get();

echo "Found " . $capitalAnswers->count() . " answers with capital Ya/Tidak\n";

if ($capitalAnswers->count() === 0) {
    echo "\n✓ No capital letters found. Data already normalized!\n\n";
    exit(0);
}

echo "\nNormalizing...\n";
echo str_repeat("-", 60) . "\n";

$updated = 0;
foreach ($capitalAnswers as $jawaban) {
    $oldValue = $jawaban->nilai;
    
    // Normalize to lowercase
    $newValue = strtolower($oldValue);
    
    // Update
    DB::table('f01_jawaban')
        ->where('id', $jawaban->id)
        ->update(['nilai' => json_encode($newValue)]);
    
    echo "Q{$jawaban->pertanyaan_id} (P_ID: {$jawaban->f01_pengisian_id}): ";
    echo "'{$oldValue}' → '{$newValue}'\n";
    
    $updated++;
}

echo str_repeat("-", 60) . "\n";
echo "\n✓ Successfully normalized {$updated} answers\n";

// Verify
echo "\nVerifying...\n";
$remaining = F01Jawaban::whereIn('pertanyaan_id', $yesnoQuestions)
    ->where(function($q) {
        $q->whereRaw("JSON_UNQUOTE(nilai) = 'Ya'")
          ->orWhereRaw("JSON_UNQUOTE(nilai) = 'Tidak'");
    })
    ->count();

if ($remaining === 0) {
    echo "✓ All capital answers have been normalized!\n\n";
} else {
    echo "⚠ WARNING: {$remaining} capital answers still remain\n\n";
}
?>
