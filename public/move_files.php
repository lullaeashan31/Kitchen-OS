<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Storage;

$privateDisk = Storage::disk('local'); // local maps to app/private
$publicDisk = Storage::disk('public'); // public maps to app/public

$files = $privateDisk->allFiles('attendance');
echo "Moving " . count($files) . " files...\n";

foreach ($files as $file) {
    if ($privateDisk->exists($file)) {
        $content = $privateDisk->get($file);
        $publicDisk->put($file, $content);
        echo "Moved: $file\n";
    }
}
echo "Done.";
