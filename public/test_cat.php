<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Http\Kernel::class)->handle(Illuminate\Http\Request::capture());

echo "Debug Categories:\n";
$kitchen = \App\Models\Kitchen::where('slug', 'churu')->first();
if (!$kitchen) {
    echo "Kitchen churu not found\n";
    exit;
}

$scoped = \App\Models\Category::where('kitchen_id', $kitchen->id)->get();
$unscoped = \App\Models\Category::withoutGlobalScopes()->get();

echo "Scoped Count: " . $scoped->count() . "\n";
echo "Unscoped Count: " . $unscoped->count() . "\n";

echo "\nScoped:\n";
foreach($scoped as $c) {
    echo "- [{$c->id}] {$c->name} (Type: {$c->type}, Kitchen: {$c->kitchen_id})\n";
}

echo "\nUnscoped not in scoped:\n";
foreach($unscoped as $c) {
    if (!$scoped->contains('id', $c->id)) {
        echo "- [{$c->id}] {$c->name} (Type: {$c->type}, Kitchen: {$c->kitchen_id})\n";
    }
}
