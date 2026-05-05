<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

try {
    if (!Schema::hasTable('purchase_batches')) {
        echo "Creating purchase_batches table...\n";
        Schema::create('purchase_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kitchen_id')->constrained()->onDelete('cascade');
            $table->foreignId('purchase_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('ingredient_id')->constrained()->onDelete('cascade');
            $table->decimal('quantity_initial', 15, 3)->nullable();
            $table->decimal('quantity_remaining', 15, 3);
            $table->decimal('price_per_unit', 15, 2);
            $table->timestamps();
        });
        echo "Table created successfully.\n";
    } else {
        echo "Table purchase_batches already exists.\n";
        if (!Schema::hasColumn('purchase_batches', 'quantity_initial')) {
            echo "Adding quantity_initial column...\n";
            Schema::table('purchase_batches', function (Blueprint $table) {
                $table->decimal('quantity_initial', 15, 3)->after('ingredient_id')->nullable();
            });
            echo "Column added successfully.\n";
        }
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
