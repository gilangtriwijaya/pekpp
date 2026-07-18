<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$type = Illuminate\Support\Facades\Schema::getColumnType("pendataan_jawaban", "nilai");
echo "TYPE: " . $type . "\n";
