<?php
require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n";
echo str_repeat("=", 100) . "\n";
echo "🔍 ANALISIS DAMPAK PENGHAPUSAN TOKEN F03\n";
echo str_repeat("=", 100) . "\n\n";

// Cek struktur token
echo "1️⃣  STRUKTUR F03 TOKEN:\n";
echo str_repeat("-", 100) . "\n";

$tokenSchema = DB::getSchemaBuilder()->getColumnListing('f03_token');
echo "Kolom-kolom dalam tabel f03_token:\n";
foreach ($tokenSchema as $col) {
    echo "  • {$col}\n";
}

// Ambil semua token untuk Dinas Pendidikan
echo "\n2️⃣  DETAIL TOKEN DINAS PENDIDIKAN PEMUDA DAN OLAHRAGA:\n";
echo str_repeat("-", 100) . "\n";

$token = DB::table('f03_token')
    ->where('upp_id', 20)
    ->first();

if ($token) {
    echo "Token ID: {$token->id}\n";
    echo "UPP ID: {$token->upp_id}\n";
    echo "Periode ID: {$token->periode_id}\n";
    echo "Token String: {$token->token}\n";
    echo "Created At: {$token->created_at}\n";
    echo "Expired At: " . ($token->expired_at ?? 'Tidak ada (permanent)') . "\n";
    
    // Cek URL format
    echo "\n📍 URL SURVEY MENGGUNAKAN TOKEN INI:\n";
    echo "  https://sistagor.anambaskab.go.id/evaluasi-yanlik/f03/token/{$token->token}\n";
    echo "  (Link ini akan TIDAK VALID setelah token dihapus)\n";
}

// Cek bagaimana token dapat dibuat
echo "\n\n3️⃣  PERTANYAAN: APAKAH PERLU GENERATE TOKEN BARU?\n";
echo str_repeat("-", 100) . "\n";

echo "✅ YA, PERLU GENERATE TOKEN BARU jika ingin:\n";
echo "   1. Kembali membuka survey untuk Dinas Pendidikan\n";
echo "   2. Mengirim ulang link survey ke responden\n\n";

echo "❌ TOKEN LAMA AKAN INVALID:\n";
echo "   1. Siapa pun yang punya link lama TIDAK BISA isi survey lagi\n";
echo "   2. Responden akan error jika klik link token lama\n\n";

echo "ℹ️  DATA RESPONDEN YANG SUDAH SUBMIT TETAP ADA:\n";
echo "   1. 28 submission tetap tersimpan (tapi akan dihapus sesuai plan)\n";
echo "   2. 392 jawaban tetap tersimpan (tapi akan dihapus sesuai plan)\n";
echo "   3. data demografis tetap tersimpan (tapi akan dihapus sesuai plan)\n\n";

// Cek cara membuat token baru
echo "\n4️⃣  CARA MEMBUAT TOKEN BARU UNTUK DINAS PENDIDIKAN:\n";
echo str_repeat("-", 100) . "\n";

$upp = DB::table('upps')->find(20);
echo "Setelah token dihapus, untuk membuat token baru:\n\n";
echo "Opsi 1: Via UI/Dashboard\n";
echo "  • Login sebagai admin\n";
echo "  • Cari UPP: {$upp->nama}\n";
echo "  • Klik 'Generate Token F03' atau sejenisnya\n";
echo "  • Sistem akan auto-generate token baru\n\n";

echo "Opsi 2: Via Command (jika ada)\n";
echo "  • php artisan f03:generate-token --upp-id=20 --periode-id=1\n\n";

// Rekomendasi
echo "\n5️⃣  PROSEDUR YANG DISARANKAN:\n";
echo str_repeat("-", 100) . "\n";

echo "Jika ingin hapus token Dinas Pendidikan:\n\n";
echo "STEP 1: Backup/Export data 28 submission yang sudah ada (opsional)\n";
echo "        Anda mungkin ingin save data sebelum dihapus\n\n";

echo "STEP 2: Hapus token + semua submission F03\n";
echo "        • 1 token\n";
echo "        • 28 pengisian\n";
echo "        • 392 jawaban\n";
echo "        • 28 data demografis\n\n";

echo "STEP 3: Generate token BARU untuk Dinas Pendidikan\n";
echo "        • Token baru otomatis di-generate via UI/command\n";
echo "        • Dapatkan link baru\n";
echo "        • Kirim link baru ke responden\n\n";

echo str_repeat("=", 100) . "\n";
echo "✅ KESIMPULAN:\n";
echo str_repeat("=", 100) . "\n\n";

echo "Q: Apakah perlu generate token baru?\n";
echo "A: YA, jika ingin buka survey lagi untuk Dinas Pendidikan\n\n";

echo "Q: Siapa yang buat token baru?\n";
echo "A: Admin/validator bisa generate di UI atau via command\n\n";

echo "Q: Berapa lama generate token baru?\n";
echo "A: Sekitar 2-3 menit (cepat)\n\n";

echo str_repeat("=", 100) . "\n\n";
