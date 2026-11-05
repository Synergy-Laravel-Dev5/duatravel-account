<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cols = \Illuminate\Support\Facades\Schema::getColumnListing('hotels');
echo "Columns: " . implode(', ', $cols) . PHP_EOL;

$hs = App\Models\Hotel::take(30)->get();
foreach($hs as $h) {
    echo $h->id . ' | ' . $h->name . ' | place: ' . json_encode($h->place) . ' | address: ' . json_encode($h->address) . PHP_EOL;
}
