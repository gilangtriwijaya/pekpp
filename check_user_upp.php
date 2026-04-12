<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\User;
use App\Models\UserUpp;
use App\Models\Upp;
use Illuminate\Support\Facades\DB;

// Initialize Laravel app
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "USER UPP DATA ANALYSIS REPORT\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Get statistics
$totalUsers = User::count();
$totalUpps = Upp::count();
$totalUserUpps = UserUpp::count();
$usersWithUpp = UserUpp::distinct('user_id')->count();
$usersWithoutUpp = $totalUsers - $usersWithUpp;

echo "📊 OVERVIEW STATISTICS:\n";
echo "───────────────────────────────────────────────────────────────\n";
echo "  Total Users: " . $totalUsers . "\n";
echo "  Total UPPs: " . $totalUpps . "\n";
echo "  Total User-UPP Assignments: " . $totalUserUpps . "\n";
echo "  Users with UPP assignments: " . $usersWithUpp . " (" . round(($usersWithUpp/$totalUsers)*100, 1) . "%)\n";
echo "  Users without UPP assignments: " . $usersWithoutUpp . " (" . round(($usersWithoutUpp/$totalUsers)*100, 1) . "%)\n\n";

// Users WITH UPP assignments
echo "───────────────────────────────────────────────────────────────\n";
echo "👥 USERS WITH UPP ASSIGNMENTS (" . $usersWithUpp . "):\n";
echo "───────────────────────────────────────────────────────────────\n\n";

$usersWithAssignments = UserUpp::with('user', 'upp')
    ->groupBy('user_id')
    ->select('user_id', DB::raw('COUNT(*) as upp_count'))
    ->get();

$count = 0;
foreach ($usersWithAssignments as $assignment) {
    $count++;
    $user = $assignment->user;
    $userUpps = UserUpp::where('user_id', $user->id)->with('upp')->get();
    $aktifCount = $userUpps->where('aktif', 1)->count();
    
    echo "  " . $count . ". " . $user->nama . "\n";
    echo "     Email: " . ($user->email ?? "N/A") . "\n";
    echo "     Total UPPs: " . $userUpps->count() . " | Active: " . $aktifCount . "\n";
    
    // List UPPs
    foreach ($userUpps as $uu) {
        $status = $uu->aktif ? "✅" : "❌";
        echo "        $status " . $uu->upp->nama . " (Peran: " . $uu->peran . ")\n";
    }
    echo "\n";
    
    if ($count >= 20) {
        echo "   ... and " . ($usersWithAssignments->count() - 20) . " more users\n\n";
        break;
    }
}

// Users WITHOUT UPP assignments
echo "───────────────────────────────────────────────────────────────\n";
echo "⚠️  USERS WITHOUT UPP ASSIGNMENTS (" . $usersWithoutUpp . "):\n";
echo "───────────────────────────────────────────────────────────────\n\n";

if ($usersWithoutUpp > 0) {
    $usersNoUpps = User::whereNotIn('id', UserUpp::pluck('user_id')->toArray())
        ->orderBy('nama')
        ->get();
    
    $count = 0;
    foreach ($usersNoUpps as $user) {
        $count++;
        echo "  " . $count . ". " . $user->nama . "\n";
        echo "     Email: " . ($user->email ?? "N/A") . "\n";
        echo "     NIP: " . ($user->nip ?? "N/A") . "\n";
        echo "     Status: " . ($user->aktif ? "Aktif" : "Nonaktif") . "\n";
        echo "     SSO ID: " . ($user->sso_user_id ?? "N/A") . "\n\n";
        
        if ($count >= 25) {
            echo "   ... and " . ($usersNoUpps->count() - 25) . " more users\n\n";
            break;
        }
    }
} else {
    echo "  ✅ All users have UPP assignments!\n\n";
}

// User-UPP Assignment Details
echo "───────────────────────────────────────────────────────────────\n";
echo "📋 USER-UPP ASSIGNMENT BREAKDOWN:\n";
echo "───────────────────────────────────────────────────────────────\n\n";

$peranDistribution = UserUpp::select('peran', DB::raw('COUNT(*) as count'))
    ->groupBy('peran')
    ->orderBy('count', 'desc')
    ->get();

echo "  Distribution by Role (Peran):\n";
foreach ($peranDistribution as $peran) {
    $bar = str_repeat("█", round($peran->count / 2));
    echo "    • " . $peran->peran . ": " . $peran->count . " " . $bar . "\n";
}

echo "\n  Active vs Inactive:\n";
$aktifCount = UserUpp::where('aktif', 1)->count();
$nonaktifCount = UserUpp::where('aktif', 0)->count();
$aktifBar = str_repeat("█", round($aktifCount / 2));
$nonaktifBar = str_repeat("█", round($nonaktifCount / 2));
echo "    • Active: " . $aktifCount . " " . $aktifBar . "\n";
echo "    • Inactive: " . $nonaktifCount . " " . $nonaktifBar . "\n";

echo "\n";

// UPP Coverage
echo "───────────────────────────────────────────────────────────────\n";
echo "🏢 UPP COVERAGE:\n";
echo "───────────────────────────────────────────────────────────────\n\n";

$uppCoverage = Upp::select('id', 'nama')
    ->withCount('userUpps')
    ->orderBy('user_upps_count', 'desc')
    ->take(15)
    ->get();

echo "  Top UPPs with most user assignments:\n\n";
foreach ($uppCoverage as $upp) {
    $bar = str_repeat("█", $upp->user_upps_count);
    echo "    " . $upp->nama . "\n";
    echo "    └─ Users assigned: " . $upp->user_upps_count . " " . $bar . "\n";
}

$uppsWithoutUsers = Upp::whereNotIn('id', UserUpp::pluck('upp_id')->toArray())->count();
if ($uppsWithoutUsers > 0) {
    echo "\n  ⚠️  UPPs without any user assignments: " . $uppsWithoutUsers . "\n";
}

echo "\n";

// Summary
echo "───────────────────────────────────────────────────────────────\n";
echo "📈 SUMMARY:\n";
echo "───────────────────────────────────────────────────────────────\n\n";

echo "  ✓ User-UPP Setup Status:\n";
if ($usersWithoutUpp === 0) {
    echo "    ✅ All users have UPP assignments\n";
} else {
    echo "    ⚠️  " . $usersWithoutUpp . " users need UPP assignments\n";
}

if ($uppsWithoutUsers === 0) {
    echo "    ✅ All UPPs have user assignments\n";
} else {
    echo "    ⚠️  " . $uppsWithoutUsers . " UPPs have no users assigned\n";
}

echo "    ✓ Average users per UPP: " . number_format($totalUserUpps / $totalUpps, 1) . "\n";
echo "    ✓ Coverage rate: " . round(($usersWithUpp / $totalUsers) * 100, 1) . "%\n";

echo "\n═══════════════════════════════════════════════════════════════\n\n";
