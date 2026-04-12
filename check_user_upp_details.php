<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\User;
use App\Models\UserUpp;
use App\Models\Upp;

// Initialize Laravel app
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "USERS WITHOUT UPP ASSIGNMENTS - DETAILED LIST\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$usersNoUpps = User::whereNotIn('id', UserUpp::pluck('user_id')->toArray())
    ->orderBy('nama')
    ->get();

echo "📍 COMPLETE LIST OF " . $usersNoUpps->count() . " USERS WITHOUT UPP:\n";
echo "───────────────────────────────────────────────────────────────\n\n";

foreach ($usersNoUpps as $idx => $user) {
    echo ($idx + 1) . ". " . $user->nama . "\n";
    echo "   Email: " . ($user->email ?? "N/A") . "\n";
    echo "   SSO ID: " . ($user->sso_user_id ?? "N/A") . "\n";
    echo "   Aktif: " . ($user->aktif ? "✅ Yes" : "❌ No") . "\n\n";
}

echo "═══════════════════════════════════════════════════════════════\n";
echo "UPPS WITHOUT ANY USER ASSIGNMENTS - DETAILED LIST\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$uppsNoUsers = Upp::whereNotIn('id', UserUpp::pluck('upp_id')->toArray())
    ->with('opd')
    ->orderBy('nama')
    ->get();

echo "📍 COMPLETE LIST OF " . $uppsNoUsers->count() . " UPPs WITHOUT USERS:\n";
echo "───────────────────────────────────────────────────────────────\n\n";

foreach ($uppsNoUsers as $idx => $upp) {
    $opdName = $upp->opd ? $upp->opd->nama : "No OPD";
    echo ($idx + 1) . ". " . $upp->nama . "\n";
    echo "   ID: " . $upp->id . "\n";
    echo "   Kode: " . ($upp->kode ?? "N/A") . "\n";
    echo "   Jenis: " . ($upp->jenis ?? "N/A") . "\n";
    echo "   OPD: " . $opdName . "\n";
    echo "   Status: " . ($upp->aktif ? "✅ Aktif" : "❌ Nonaktif") . "\n\n";
}

echo "═══════════════════════════════════════════════════════════════\n\n";
