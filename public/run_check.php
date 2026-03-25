<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$attendances = \App\Models\Attendance::latest()->get();
echo "<pre>";
foreach ($attendances as $a) {
    echo "ID: {$a->id} | Staff: {$a->staff_code} | Path: {$a->selfie_path_in}\n";
    echo "Disk: " . config('filesystems.default') . "\n";
    echo "Exists locally: " . (\Illuminate\Support\Facades\Storage::disk(config('filesystems.default'))->exists($a->selfie_path_in) ? 'Yes' : 'No') . "\n";
    echo "Exists public: " . (\Illuminate\Support\Facades\Storage::disk('public')->exists($a->selfie_path_in) ? 'Yes' : 'No') . "\n";
}
echo "</pre>";
