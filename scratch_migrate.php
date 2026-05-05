<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$status = $kernel->call('migrate', ['--force' => true]);
echo "Migration exit code: $status\n";
echo "Output: " . \Illuminate\Support\Facades\Artisan::output() . "\n";
