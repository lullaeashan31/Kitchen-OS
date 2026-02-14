<?php

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Purchase;
use App\Models\Vendor;

echo "Migrating Vendors...\n";

$purchases = Purchase::whereNull('vendor_id')->whereNotNull('vendor')->get();

foreach ($purchases as $purchase) {
    if (empty($purchase->vendor))
        continue;

    $vendorName = trim($purchase->vendor);

    // Find or Create Vendor
    $vendor = Vendor::firstOrCreate(
        ['name' => $vendorName],
        ['address' => 'Imported from Purchase History']
    );

    // Update Purchase
    $purchase->vendor_id = $vendor->id;
    $purchase->save();

    echo "Linked Purchase #{$purchase->id} to Vendor '{$vendor->name}'\n";
}

echo "Migration Complete.\n";
