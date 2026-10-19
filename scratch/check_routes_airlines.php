<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Airlines count: " . \App\Models\Airline::count() . "\n";
foreach (\App\Models\Airline::all() as $a) {
    echo "- " . $a->name . " (" . ($a->code ?: $a->iata_code) . ")\n";
}
echo "Routes count: " . \App\Models\Route::count() . "\n";
foreach (\App\Models\Route::all() as $r) {
    echo "- " . $r->formatted_route . "\n";
}

echo "Airlines cols: " . implode(", ", \Illuminate\Support\Facades\Schema::getColumnListing("airlines")) . "\n";
echo "Routes cols: " . implode(", ", \Illuminate\Support\Facades\Schema::getColumnListing("routes")) . "\n";
