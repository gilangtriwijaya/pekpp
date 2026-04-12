<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\F01Pengisian;

$pengisianId = 1;
$pengisian = F01Pengisian::with('periode', 'upp')->findOrFail($pengisianId);
$pengisian->load('jawaban.pertanyaan');

echo "=== Database Check ===\n";
echo "Total jawaban in DB for pengisian $pengisianId: " . $pengisian->jawaban->count() . "\n";

echo "\nFirst 10 jawaban:\n";
$pengisian->jawaban->take(10)->each(function($j) {
    echo "Q" . $j->pertanyaan_id . ": nilai=" . json_encode($j->nilai) . "\n";
});

echo "\n=== Testing keyBy (what API does) ===\n";

$jawaban = $pengisian->jawaban()->with('pertanyaan')->get()->keyBy('pertanyaan_id');

echo "Jawaban keyed by pertanyaan_id: " . $jawaban->count() . " records\n";

echo "\nChecking if Q1-Q10 can be retrieved:\n";
for ($i = 1; $i <= 10; $i++) {
    $answer = $jawaban->get($i);
    if ($answer) {
        $val = json_encode($answer->nilai);
        if (strlen($val) > 60) $val = substr($val, 0, 60) . "...";
        echo "Q$i: FOUND - " . $val . "\n";
    } else {
        echo "Q$i: NOT FOUND\n";
    }
}

echo "\n=== Done ===\n";
