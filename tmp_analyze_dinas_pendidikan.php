<?php
require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n";
echo str_repeat("=", 100) . "\n";
echo "🔍 ANALISIS: Hapus Token & Isian F03 untuk Dinas Pendidikan Pemuda dan Olahraga\n";
echo str_repeat("=", 100) . "\n\n";

// Cari UPP
echo "1️⃣  CARI UPP 'Dinas Pendidikan Pemuda dan Olahraga':\n";
echo str_repeat("-", 100) . "\n";

$upp = DB::table('upps')
    ->where('nama', 'like', '%Pendidikan%')
    ->orWhere('nama', 'like', '%Dinas Pendidikan%')
    ->get();

if ($upp->isEmpty()) {
    echo "❌ UPP tidak ditemukan dengan nama yang mirip.\n\n";
    echo "Search dalam database untuk mencari UPP:\n";
    $allUpps = DB::table('upps')->select('id', 'nama')->get();
    foreach ($allUpps as $u) {
        echo "  ID: {$u->id}, Nama: {$u->nama}\n";
    }
    exit;
}

foreach ($upp as $u) {
    echo "✓ ID: {$u->id}\n";
    echo "  Nama: {$u->nama}\n";
    $uppId = $u->id;
}

// Cari Token F03
echo "\n2️⃣  TOKEN F03 UNTUK UPP TERSEBUT:\n";
echo str_repeat("-", 100) . "\n";

$tokens = DB::table('f03_token')
    ->where('upp_id', $uppId)
    ->get();

echo "Total Token: " . count($tokens) . "\n\n";

foreach ($tokens as $token) {
    echo "  ID: {$token->id}\n";
    echo "  Token: {$token->token}\n";
    if (property_exists($token, 'status')) {
        echo "  Status: {$token->status}\n";
    }
    echo "  Created At: {$token->created_at}\n";
    echo "\n";
}

// Cari F03 Pengisian
echo "\n3️⃣  DATA F03 PENGISIAN (SUBMISSION SURVEY):\n";
echo str_repeat("-", 100) . "\n";

$pengisian = DB::table('f03_pengisian')
    ->where('upp_id', $uppId)
    ->get();

echo "Total Pengisian: " . count($pengisian) . "\n\n";

foreach ($pengisian as $p) {
    echo "  ID: {$p->id}\n";
    echo "  Periode: {$p->periode_id}\n";
    echo "  Token: {$p->f03_token_id}\n";
    echo "  Response Date: {$p->response_date}\n";
    echo "  Created At: {$p->created_at}\n";
    echo "\n";
}

// Cari F03 Jawaban
echo "4️⃣  DATA F03 JAWABAN (RESPONSES):\n";
echo str_repeat("-", 100) . "\n";

if (count($pengisian) > 0) {
    $pengisianIds = collect($pengisian)->pluck('id')->toArray();
    
    $jawaban = DB::table('f03_jawaban')
        ->whereIn('f03_pengisian_id', $pengisianIds)
        ->get();
    
    echo "Total Jawaban: " . count($jawaban) . "\n";
    if (count($jawaban) > 0) {
        echo "  (Ada " . count($jawaban) . " jawaban yang akan dihapus)\n";
    }
    echo "\n";
} else {
    echo "Tidak ada pengisian, jadi tidak ada jawaban.\n\n";
}

// Cari F03 Response Demographic
echo "5️⃣  DATA F03 RESPONSE DEMOGRAPHIC:\n";
echo str_repeat("-", 100) . "\n";

if (count($pengisian) > 0) {
    $pengisianIds = collect($pengisian)->pluck('id')->toArray();
    
    $demographic = DB::table('f03_response_demographics')
        ->whereIn('f03_pengisian_id', $pengisianIds)
        ->get();
    
    echo "Total Response Demographic: " . count($demographic) . "\n";
    if (count($demographic) > 0) {
        echo "  (Ada " . count($demographic) . " data demografis yang akan dihapus)\n";
    }
    echo "\n";
} else {
    echo "Tidak ada pengisian, jadi tidak ada data demografis.\n\n";
}

// Summary
echo str_repeat("=", 100) . "\n";
echo "📊 SUMMARY DATA YANG AKAN DIHAPUS:\n";
echo str_repeat("=", 100) . "\n\n";

echo "UPP: {$upp->first()->nama}\n";
echo "UPP ID: {$uppId}\n\n";

echo "Yang akan dihapus:\n";
echo "  1. F03 Token: " . count($tokens) . " record\n";
echo "  2. F03 Pengisian: " . count($pengisian) . " record\n";

if (count($pengisian) > 0) {
    $jawaban = DB::table('f03_jawaban')
        ->whereIn('f03_pengisian_id', collect($pengisian)->pluck('id')->toArray())
        ->count();
    echo "  3. F03 Jawaban: {$jawaban} record\n";
    
    $demographic = DB::table('f03_response_demographics')
        ->whereIn('f03_pengisian_id', collect($pengisian)->pluck('id')->toArray())
        ->count();
    echo "  4. F03 Response Demographic: {$demographic} record\n";
}

echo "\n";
echo "⚠️  YANG AMAN (TIDAK DIHAPUS):\n";
echo "  ✓ F03 Aspek Template - AMAN\n";
echo "  ✓ F03 Indikator Template - AMAN\n";
echo "  ✓ UPP Data - AMAN\n";
echo "  ✓ Aspek/Indikator/Pertanyaan Master - AMAN\n";

echo "\n" . str_repeat("=", 100) . "\n";
echo "✅ ANALISIS SELESAI - SIAP UNTUK DIHAPUS\n";
echo str_repeat("=", 100) . "\n\n";
