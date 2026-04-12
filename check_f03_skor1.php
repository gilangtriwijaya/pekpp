<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Upp;
use App\Models\F03Pengisian;
use App\Models\F03Jawaban;
use App\Models\F03Indikator;
use Illuminate\Support\Facades\DB;

// Initialize Laravel app
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "\n";
echo str_repeat("=", 100) . "\n";
echo "🔍 CEK F03 RESPONSES - DINAS PENDIDIKAN PEMUDA DAN OLAHRAGA DENGAN SKOR 1\n";
echo str_repeat("=", 100) . "\n";

// Cari Dinas Pendidikan Pemuda dan Olahraga
$dinasKeywords = ['pendidikan', 'pemuda', 'olahraga'];
$dinases = Upp::whereRaw('LOWER(nama) LIKE ?', ['%pendidikan%'])
    ->orWhereRaw('LOWER(nama) LIKE ?', ['%pemuda%'])
    ->orWhereRaw('LOWER(nama) LIKE ?', ['%olahraga%'])
    ->get();

echo "\n📍 Dinas yang ditemukan:\n";
foreach ($dinases as $dinas) {
    echo "  - UPP ID {$dinas->id}: {$dinas->nama}\n";
}

if ($dinases->isEmpty()) {
    echo "  ⚠️ Tidak ada UPP yang cocok dengan nama Pendidikan/Pemuda/Olahraga\n";
    echo "\n📋 UPP yang tersedia:\n";
    Upp::limit(20)->get()->each(function($upp) {
        echo "  - UPP ID {$upp->id}: {$upp->nama}\n";
    });
    exit;
}

// Per masing-masing dinas, cari F03 responses dengan skor 1
foreach ($dinases as $dinas) {
    echo "\n" . str_repeat("-", 100) . "\n";
    echo "🏢 DINAS: {$dinas->nama} (ID: {$dinas->id})\n";
    echo str_repeat("-", 100) . "\n";

    // Get F03 responses
    $responses = F03Pengisian::where('upp_id', $dinas->id)
        ->with(['periode', 'jawaban.indikator', 'token'])
        ->orderBy('response_date', 'desc')
        ->get();

    if ($responses->isEmpty()) {
        echo "  ℹ️ Tidak ada F03 responses untuk dinas ini\n";
        continue;
    }

    echo "\n  📊 Total Responses: {$responses->count()}\n\n";

    // Analisis setiap response
    foreach ($responses as $resp) {
        $avgScore = $resp->jawaban()->avg('score');
        $countScore1 = $resp->jawaban()->where('score', 1)->count();
        
        if ($countScore1 > 0) {
            echo "  ✓ Response ID: {$resp->id}\n";
            echo "    Periode: {$resp->periode->nama}\n";
            echo "    Tanggal: {$resp->response_date}\n";
            echo "    Rata-rata Skor: " . number_format($avgScore, 2) . "\n";
            echo "    🔴 Jawaban dengan Skor 1: {$countScore1} / {$resp->jawaban->count()}\n";
            
            // Detail jawaban dengan skor 1
            $jawaban1 = $resp->jawaban()->where('score', 1)->get();
            foreach ($jawaban1 as $jw) {
                echo "      - Indikator: {$jw->indikator->kode} | {$jw->indikator->pertanyaan}\n";
                echo "        Skor: {$jw->score}\n";
                if ($jw->response_text) {
                    echo "        Response: {$jw->response_text}\n";
                }
                if ($jw->catatan) {
                    echo "        Catatan: {$jw->catatan}\n";
                }
            }
            
            // Summary semua jawaban
            echo "    📋 Summary semua jawaban:\n";
            $groupByScore = $resp->jawaban()->selectRaw('score, COUNT(*) as count')
                ->groupBy('score')
                ->orderBy('score', 'desc')
                ->get();
            
            foreach ($groupByScore as $group) {
                $scoreLabel = match($group->score) {
                    5 => 'Sangat Baik',
                    4 => 'Baik',
                    3 => 'Cukup',
                    2 => 'Kurang',
                    1 => 'Sangat Kurang',
                    default => 'Unknown'
                };
                echo "       {$group->score} ({$scoreLabel}): {$group->count} jawaban\n";
            }
            echo "\n";
        }
    }

    // Summary per indikator untuk dinas ini
    echo "\n  📈 ANALISIS PER INDIKATOR (Semua Response):\n";
    $indikatorStats = DB::table('f03_jawaban')
        ->join('f03_pengisian', 'f03_jawaban.f03_pengisian_id', '=', 'f03_pengisian.id')
        ->join('f03_indikator', 'f03_jawaban.f03_indikator_id', '=', 'f03_indikator.id')
        ->where('f03_pengisian.upp_id', $dinas->id)
        ->selectRaw('f03_indikator.id, f03_indikator.kode, f03_indikator.pertanyaan, 
                   AVG(f03_jawaban.score) as avg_score,
                   COUNT(f03_jawaban.id) as total_jawaban,
                   SUM(CASE WHEN f03_jawaban.score = 1 THEN 1 ELSE 0 END) as skor_1,
                   SUM(CASE WHEN f03_jawaban.score = 2 THEN 1 ELSE 0 END) as skor_2,
                   SUM(CASE WHEN f03_jawaban.score = 3 THEN 1 ELSE 0 END) as skor_3,
                   SUM(CASE WHEN f03_jawaban.score = 4 THEN 1 ELSE 0 END) as skor_4,
                   SUM(CASE WHEN f03_jawaban.score = 5 THEN 1 ELSE 0 END) as skor_5')
        ->groupBy('f03_indikator.id', 'f03_indikator.kode', 'f03_indikator.pertanyaan')
        ->orderBy('avg_score', 'asc')
        ->get();

    foreach ($indikatorStats as $stat) {
        echo "    Kode: {$stat->kode}\n";
        echo "    Pertanyaan: " . substr($stat->pertanyaan, 0, 80) . (strlen($stat->pertanyaan) > 80 ? '...' : '') . "\n";
        echo "    Rata-rata: " . number_format($stat->avg_score, 2) . "\n";
        echo "    Distribusi: 5={$stat->skor_5} | 4={$stat->skor_4} | 3={$stat->skor_3} | 2={$stat->skor_2} | 🔴 1={$stat->skor_1}\n";
        if ($stat->skor_1 > 0) {
            $percentage = ($stat->skor_1 / $stat->total_jawaban) * 100;
            echo "    ⚠️ {$stat->skor_1} dari {$stat->total_jawaban} (" . number_format($percentage, 1) . "%)\n";
        }
        echo "\n";
    }
}

echo str_repeat("=", 100) . "\n";
echo "✅ Selesai\n\n";
