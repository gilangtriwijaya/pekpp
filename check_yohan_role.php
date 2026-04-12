<?php
// Load Laravel app
require __DIR__ . '/bootstrap/app.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Get database connection
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';

// Now query the user
$user = \App\Models\User::where('nama', 'like', '%yohan%')->first();

if ($user) {
  echo "=" . str_repeat("=", 50) . "\n";
  echo "USER INFO: " . $user->nama . "\n";
  echo "=" . str_repeat("=", 50) . "\n";
  echo "Email: " . $user->email . "\n";
  echo "NIP: " . ($user->nip ?? 'NULL') . "\n";
  echo "Role SSO: " . ($user->role_sso ?? 'NULL - NO GLOBAL ROLE') . "\n";
  echo "SSO User ID: " . ($user->sso_user_id ?? 'NULL') . "\n";
  echo "Aktif: " . ($user->aktif ? 'Yes' : 'No') . "\n\n";
  
  echo "USER UPP ASSIGNMENTS:\n";
  echo str_repeat("-", 50) . "\n";
  $userUpps = $user->userUpps()->with('upp')->get();
  if ($userUpps->count() > 0) {
    foreach ($userUpps as $uu) {
      echo "UPP: " . ($uu->upp->nama ?? 'Unknown') . "\n";
      echo "  Role: " . $uu->peran . "\n";
      echo "  Active: " . ($uu->aktif ? 'Yes' : 'No') . "\n";
    }
  } else {
    echo "No UPP assignments\n";
  }
  
  echo "\n" . str_repeat("-", 50) . "\n";
  echo "ROLE CHECKS:\n";
  echo str_repeat("-", 50) . "\n";
  echo "Has Superadmin Role? " . ($user->hasGlobalRole('superadmin') ? 'YES ✅' : 'NO ❌') . "\n";
  echo "Has Admin Organisasi Role? " . ($user->hasGlobalRole('admin_organisasi') ? 'YES ✅' : 'NO ❌') . "\n";
  echo "Has Admin Bagian Organisasi? " . ($user->hasGlobalRole('admin_bagian_organisasi') ? 'YES ✅' : 'NO ❌') . "\n";
  
} else {
  echo "❌ User 'yohan' not found in database\n";
  
  // List all superadmin users
  echo "\nAll users with 'superadmin' role:\n";
  $superadmins = \App\Models\User::where('role_sso', 'superadmin')->get();
  foreach ($superadmins as $admin) {
    echo "  - " . $admin->nama . " (Email: " . $admin->email . ")\n";
  }
}
