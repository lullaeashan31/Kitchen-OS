<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$categories = \App\Models\Category::withoutGlobalScopes()->get();
foreach ($categories as $cat) {
    echo "ID: {$cat->id}, Name: {$cat->name}, Type: {$cat->type}, Kitchen: {$cat->kitchen_id}\n";
}

