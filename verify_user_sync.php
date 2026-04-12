<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\User;
use App\Models\SsoAllowedOpd;
use App\Models\UserUpp;
use Illuminate\Support\Facades\DB;

// Initialize Laravel app
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "USER SYNC VERIFICATION REPORT\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Get all statistics
$totalUsers = User::count();
$usersWithSsoId = User::whereNotNull('sso_user_id')->count();
$usersAktif = User::where('aktif', 1)->count();
$usersNonAktif = User::where('aktif', 0)->count();

echo "📊 USER STATISTICS:\n";
echo "   • Total Users: " . $totalUsers . "\n";
echo "   • Users with SSO ID: " . $usersWithSsoId . "\n";
echo "   • Active Users: " . $usersAktif . "\n";
echo "   • Inactive Users: " . $usersNonAktif . "\n\n";

// Check sync status
$usersWithLastSync = User::whereNotNull('last_sync_at')->count();
echo "   • Last synced: " . $usersWithLastSync . " users\n\n";

// Check UPP assignments
$usersWithUpp = UserUpp::distinct('user_id')->count();
$userUppRecords = UserUpp::where('aktif', 1)->count();

echo "   • Users assigned to UPP: " . $usersWithUpp . "\n";
echo "   • Active UPP assignments: " . $userUppRecords . "\n\n";

// Check allowed OPDs
echo "───────────────────────────────────────────────────────────────\n";
echo "📋 SSO ALLOWED OPDs MAPPING:\n";
echo "───────────────────────────────────────────────────────────────\n\n";

$totalAllowedOpdRecords = SsoAllowedOpd::count();
$usersWithAllowedOpds = SsoAllowedOpd::distinct('user_id')->count();

echo "   • Total allowed OPD mappings: " . $totalAllowedOpdRecords . "\n";
echo "   • Users with OPD restrictions: " . $usersWithAllowedOpds . "\n";
echo "   • Users with global access: " . ($totalUsers - $usersWithAllowedOpds) . "\n\n";

// List users with SSO info
echo "───────────────────────────────────────────────────────────────\n";
echo "👥 USER DETAILS:\n";
echo "───────────────────────────────────────────────────────────────\n\n";

$users = User::select('id', 'nama', 'email', 'nip', 'sso_user_id', 'aktif', 'last_sync_at', 'role_sso')
    ->orderBy('nama')
    ->get();

$count = 0;
foreach ($users as $user) {
    $count++;
    if ($count > 20) {
        echo "   ... and " . ($users->count() - 20) . " more users\n";
        break;
    }
    
    $status = $user->aktif ? "✅" : "❌";
    echo $status . " " . $user->nama . "\n";
    echo "   Email: " . ($user->email ?? "N/A") . " | NIP: " . ($user->nip ?? "N/A") . "\n";
    
    if ($user->sso_user_id) {
        echo "   SSO ID: " . $user->sso_user_id . " | Last Sync: " . ($user->last_sync_at ? $user->last_sync_at->format('Y-m-d H:i:s') : "N/A") . "\n";
    }
    
    // Check UPP assignments
    $upps = UserUpp::where('user_id', $user->id)->where('aktif', 1)->get();
    if ($upps->count() > 0) {
        echo "   UPPs: " . implode(", ", $upps->pluck('upp_id')->toArray()) . " (" . $upps->count() . " role(s))\n";
    }
    
    // Check allowed OPDs
    $allowedOpds = SsoAllowedOpd::where('user_id', $user->id)->get();
    if ($allowedOpds->count() > 0) {
        echo "   Allowed OPDs (pekppp): " . implode(", ", $allowedOpds->pluck('opd_id')->toArray());
        echo " | App: " . implode(", ", $allowedOpds->pluck('app_code')->unique()->toArray()) . "\n";
    }
    
    echo "\n";
}

echo "───────────────────────────────────────────────────────────────\n";
echo "📈 ROLE DISTRIBUTION (FROM SSO):\n";
echo "───────────────────────────────────────────────────────────────\n\n";

$roleCounts = DB::table('users')
    ->where('role_sso', '!=', '')
    ->whereNotNull('role_sso')
    ->select('role_sso')
    ->groupBy('role_sso')
    ->selectRaw('role_sso, COUNT(*) as count')
    ->get();

if ($roleCounts->count() > 0) {
    foreach ($roleCounts as $role) {
        echo "   • " . $role->role_sso . ": " . $role->count . " users\n";
    }
} else {
    echo "   • No SSO role data available\n";
}

echo "\n";

// Check for any sync issues
echo "───────────────────────────────────────────────────────────────\n";
echo "⚠️  DATA QUALITY CHECKS:\n";
echo "───────────────────────────────────────────────────────────────\n\n";

$usersWithoutSsoId = User::whereNull('sso_user_id')->count();
$usersWithoutEmail = User::whereNull('email')->count();
$usersWithoutLastSync = User::whereNull('last_sync_at')->count();

if ($usersWithoutSsoId > 0) {
    echo "   ⚠️  Users without SSO ID: " . $usersWithoutSsoId . "\n";
}

if ($usersWithoutEmail > 0) {
    echo "   ⚠️  Users without email: " . $usersWithoutEmail . "\n";
}

if ($usersWithoutLastSync > 0) {
    echo "   ⚠️  Users never synced: " . $usersWithoutLastSync . "\n";
}

if ($usersWithoutSsoId == 0 && $usersWithoutLastSync == 0) {
    echo "   ✅ All users properly synced from SSO\n";
}

echo "\n";

// Sync log
echo "───────────────────────────────────────────────────────────────\n";
echo "📜 LAST SYNC LOG:\n";
echo "───────────────────────────────────────────────────────────────\n\n";

$lastLog = DB::table('sso_sync_logs')
    ->where('command', 'sso:mirror-users')
    ->orderByDesc('started_at')
    ->first();

if ($lastLog) {
    echo "   Started: " . $lastLog->started_at . "\n";
    echo "   Finished: " . ($lastLog->finished_at ?? "In progress") . "\n";
    echo "   Status: " . $lastLog->status . "\n";
    echo "   Message: " . ($lastLog->message ?? "N/A") . "\n";
} else {
    echo "   No sync log found\n";
}

echo "\n";

echo "═══════════════════════════════════════════════════════════════\n\n";
