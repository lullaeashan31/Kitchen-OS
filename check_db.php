<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = DB::select('SHOW TABLES LIKE "purchase_batches"');
if (count($tables) > 0) {
    echo "Table purchase_batches EXISTS.\n";
    $columns = DB::select('SHOW COLUMNS FROM purchase_batches LIKE "quantity_initial"');
    if (count($columns) > 0) {
        echo "Column quantity_initial EXISTS.\n";
    } else {
        echo "Column quantity_initial MISSING.\n";
    }
} else {
    echo "Table purchase_batches MISSING.\n";
}
