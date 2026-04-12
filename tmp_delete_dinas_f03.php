<?php
require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n";
echo str_repeat("=", 100) . "\n";
echo "🗑️  MENGHAPUS TOKEN & ISIAN F03 DINAS PENDIDIKAN PEMUDA DAN OLAHRAGA\n";
echo str_repeat("=", 100) . "\n";

$uppId = 20;
$uppName = 'Dinas Pendidikan Pemuda dan Olahraga';

try {
    DB::beginTransaction();
    
    // Disable foreign key checks
    DB::statement('SET FOREIGN_KEY_CHECKS=0');
    
    echo "\n📊 Data yang akan dihapus:\n";
    echo str_repeat("-", 100) . "\n";
    
    // Count sebelum hapus
    $tokensCount = DB::table('f03_token')->where('upp_id', $uppId)->count();
    $pengisianIds = DB::table('f03_pengisian')->where('upp_id', $uppId)->pluck('id');
    $pengisianCount = count($pengisianIds);
    $jawabanCount = DB::table('f03_jawaban')->whereIn('f03_pengisian_id', $pengisianIds)->count();
    $demografiCount = DB::table('f03_response_demographics')->whereIn('f03_pengisian_id', $pengisianIds)->count();
    
    echo "  F03 Token: {$tokensCount} record\n";
    echo "  F03 Pengisian: {$pengisianCount} record\n";
    echo "  F03 Jawaban: {$jawabanCount} record\n";
    echo "  F03 Response Demographic: {$demografiCount} record\n";
    echo "  Total: " . ($tokensCount + $pengisianCount + $jawabanCount + $demografiCount) . " record\n";
    
    echo "\n🗑️  Menghapus data...\n";
    echo str_repeat("-", 100) . "\n";
    
    // Delete in order (children first, then parent)
    
    // 1. Hapus F03 Jawaban
    $deleted = DB::table('f03_jawaban')
        ->whereIn('f03_pengisian_id', $pengisianIds)
        ->delete();
    echo "  ✅ F03 Jawaban: {$deleted} record dihapus\n";
    
    // 2. Hapus F03 Response Demographic
    $deleted = DB::table('f03_response_demographics')
        ->whereIn('f03_pengisian_id', $pengisianIds)
        ->delete();
    echo "  ✅ F03 Response Demographic: {$deleted} record dihapus\n";
    
    // 3. Hapus F03 Pengisian
    $deleted = DB::table('f03_pengisian')
        ->where('upp_id', $uppId)
        ->delete();
    echo "  ✅ F03 Pengisian: {$deleted} record dihapus\n";
    
    // 4. Hapus F03 Token
    $deleted = DB::table('f03_token')
        ->where('upp_id', $uppId)
        ->delete();
    echo "  ✅ F03 Token: {$deleted} record dihapus\n";
    
    // Re-enable foreign key checks
    DB::statement('SET FOREIGN_KEY_CHECKS=1');
    
    // Commit transaction
    DB::commit();
    
    echo "\n✅ PENGHAPUSAN BERHASIL!\n";
    echo str_repeat("=", 100) . "\n\n";
    
    // Verifikasi
    echo "🔍 VERIFIKASI DATA SETELAH PENGHAPUSAN:\n";
    echo str_repeat("-", 100) . "\n";
    
    $tokensVerify = DB::table('f03_token')->where('upp_id', $uppId)->count();
    $pengisianVerify = DB::table('f03_pengisian')->where('upp_id', $uppId)->count();
    
    echo "  F03 Token untuk UPP ini: {$tokensVerify} record (harusnya 0)\n";
    echo "  F03 Pengisian untuk UPP ini: {$pengisianVerify} record (harusnya 0)\n";
    
    if ($tokensVerify == 0 && $pengisianVerify == 0) {
        echo "\n  ✅ VERIFIKASI SUKSES - DATA TELAH DIHAPUS!!\n";
    }
    
    echo "\n📋 INSTRUMEN TETAP AMAN:\n";
    echo str_repeat("-", 100) . "\n";
    
    $aspekCount = DB::table('f03_aspek')->count();
    $indikatorCount = DB::table('f03_indikator')->count();
    $tokenOther = DB::table('f03_token')->count();
    
    echo "  F03 Aspek Template: {$aspekCount} record ✓\n";
    echo "  F03 Indikator Template: {$indikatorCount} record ✓\n";
    echo "  F03 Token (UPP lain): {$tokenOther} record ✓\n";
    
    $dinas = DB::table('upps')->find(20);
    echo "  UPP '{$dinas->nama}': TETAP ADA ✓\n";
    
    echo "\n" . str_repeat("=", 100) . "\n";
    echo "✅ SELESAI!\n";
    echo "📌 Token & isian F03 Dinas Pendidikan sudah dihapus.\n";
    echo "📌 Jika ingin survey ulang, generate token baru via UI/command.\n";
    echo str_repeat("=", 100) . "\n\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    DB::statement('SET FOREIGN_KEY_CHECKS=1');
    
    echo "\n❌ ERROR SAAT PENGHAPUSAN!\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "Data TIDAK dihapus (transaction di-rollback)\n\n";
    exit(1);
}
