<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$a = \App\Models\Attendance::latest()->first();
if ($a) {
    echo "Path In: " . $a->selfie_path_in . "\n";
    echo "Path Out: " . $a->selfie_path_out . "\n";
} else {
    echo "No attendance found.\n";
}
