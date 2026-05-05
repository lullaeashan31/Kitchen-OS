<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

try {
    Log::info("FORCE DB FIX START");
    DB::statement("CREATE TABLE IF NOT EXISTS purchase_batches (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        kitchen_id BIGINT UNSIGNED NOT NULL,
        purchase_id BIGINT UNSIGNED NULL,
        ingredient_id BIGINT UNSIGNED NOT NULL,
        quantity_initial DECIMAL(15,3) NULL,
        quantity_remaining DECIMAL(15,3) NOT NULL,
        price_per_unit DECIMAL(15,2) NOT NULL,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL
    )");
    Log::info("FORCE DB FIX SUCCESS");
    echo "SUCCESS\n";
} catch (\Exception $e) {
    Log::error("FORCE DB FIX FAILED: " . $e->getMessage());
    echo "FAILED: " . $e->getMessage() . "\n";
}
