<?php
require 'vendor/autoload.php';
require 'bootstrap/app.php';

use App\Models\F01Pengisian;
use App\Models\F01Jawaban;
use App\Models\Pertanyaan;

echo "\n=== F01 JAWABAN DEBUG ===\n\n";

// Get recent pengisian
$pengisians = F01Pengisian::with(['jawaban.pertanyaan', 'periode', 'upp'])
    ->orderBy('id', 'desc')
    ->limit(5)
    ->get();

echo "Showing last 5 F01 Pengisians:\n";
echo str_repeat("=", 80) . "\n";

foreach ($pengisians as $p) {
    echo "\n📝 Pengisian ID: {$p->id}\n";
    echo "   UPP: {$p->upp->nama}\n";
    echo "   Periode: {$p->periode->nama}\n";
    echo "   Status: {$p->status}\n";
    echo "   Total Jawaban: {$p->jawaban->count()}\n";
    
    if ($p->jawaban->count() > 0) {
        echo "\n   Jawaban Details:\n";
        echo "   " . str_repeat("-", 76) . "\n";
        echo "   | Q_ID | Tipe Input | Nilai Stored | Nilai Type |\n";
        echo "   " . str_repeat("-", 76) . "\n";
        
        foreach ($p->jawaban->take(10) as $j) {
            $qId = $j->pertanyaan_id;
            $tipeInput = $j->pertanyaan->tipe_input ?? 'N/A';
            $nilaiStored = is_array($j->nilai) ? json_encode($j->nilai) : $j->nilai;
            $nilaiType = gettype($j->nilai);
            
            // Truncate for display
            $nilaiDisplay = is_string($nilaiStored) ? substr($nilaiStored, 0, 30) : $nilaiStored;
            if (strlen($nilaiStored) > 30) $nilaiDisplay .= '...';
            
            printf("   | %-4s | %-10s | %-12s | %-10s |\n", $qId, $tipeInput, $nilaiDisplay, $nilaiType);
        }
        
        if ($p->jawaban->count() > 10) {
            echo "   ... and " . ($p->jawaban->count() - 10) . " more\n";
        }
        echo "   " . str_repeat("-", 76) . "\n";
    } else {
        echo "   ⚠️  No jawaban found for this pengisian\n";
    }
}

echo "\n\n=== DETAILED CHECK: Latest Pengisian ===\n";

if ($pengisians->count() > 0) {
    $latest = $pengisians->first();
    echo "\nPengisian ID: {$latest->id}\n";
    echo "UPP: {$latest->upp->nama}\n";
    echo "Period: {$latest->periode->nama}\n";
    
    if ($latest->jawaban->count() > 0) {
        echo "\nFirst 3 answers (detailed):\n";
        foreach ($latest->jawaban->take(3) as $j) {
            echo "\n  Q{$j->pertanyaan_id} ({$j->pertanyaan->tipe_input}):\n";
            echo "    Raw value: " . var_export($j->nilai, true) . "\n";
            echo "    Type: " . gettype($j->nilai) . "\n";
            
            // Check character encoding
            if (is_string($j->nilai)) {
                echo "    String length: " . strlen($j->nilai) . "\n";
                echo "    Hex: " . bin2hex($j->nilai) . "\n";
            }
        }
    }
}

echo "\n\n=== CHECKING API RESPONSE ===\n";

if ($pengisians->count() > 0) {
    $latest = $pengisians->first();
    
    // Simulate API response for this pengisian
    $jawaban = $latest->jawaban()
        ->with('pertanyaan')
        ->get()
        ->keyBy('pertanyaan_id');
    
    echo "\nAPI would return for Pengisian {$latest->id}:\n";
    echo "Total jawaban keys: " . count($jawaban) . "\n";
    
    foreach ($jawaban->take(3) as $key => $j) {
        echo "\n  question.id: {$key}\n";
        echo "  question.nilai (from API): " . var_export($j->nilai, true) . "\n";
        echo "  Type: " . gettype($j->nilai) . "\n";
    }
}

echo "\n\n";
?>
