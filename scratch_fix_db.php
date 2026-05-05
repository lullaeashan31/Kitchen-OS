<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

use Illuminate\Support\Facades\DB;

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

try {
    DB::statement("ALTER TABLE sop_item_completions MODIFY COLUMN status ENUM('completed', 'rejected', 'resubmitted', 'submitted') DEFAULT 'submitted'");
    echo "SUCCESS: Added 'submitted' to ENUM\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
