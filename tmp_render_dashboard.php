<?php
// tmp_render_dashboard.php
// Boot Laravel, authenticate user id=1, render dashboard view and print HTML

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Use Auth facade
use Illuminate\Support\Facades\Auth;

// Login as user id=1
$user = null;
try {
    Auth::loginUsingId(1);
    $user = Auth::user();
} catch (Throwable $e) {
    echo "ERROR: failed to login using id 1: " . $e->getMessage() . "\n";
    exit(1);
}

// Render view
try {
    $html = view('dashboard', ['user' => $user])->render();
    echo $html;
} catch (Throwable $e) {
    echo "ERROR: failed to render dashboard: " . $e->getMessage() . "\n";
    exit(1);
}

