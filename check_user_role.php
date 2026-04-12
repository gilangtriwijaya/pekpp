<?php
require __DIR__ . '/bootstrap/app.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

// Bootstrap the application
$app->make(\Illuminate\Contracts\Console\Kernel::class);

use App\Models\User;
use App\Models\UserUpp;

$user = User::where('nama', 'like', '%Alsip%')->first();
if ($user) {
    echo "Found user: {$user->nama} (ID: {$user->id})\n";
    echo "Global role: {$user->role_sso}\n";
    echo "\nUPP Assignments:\n";
    $userUpps = UserUpp::where('user_id', $user->id)->with('upp')->get();
    foreach ($userUpps as $uu) {
        $uppName = $uu->upp ? $uu->upp->nama : 'N/A';
        $actif = $uu->aktif ? 'Yes' : 'No';
        echo "- UPP: {$uppName} (ID: {$uu->upp_id}), Role: {$uu->peran}, Active: {$actif}\n";
    }
} else {
    echo "User not found\n";
}
