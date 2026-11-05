<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$k = $app->make(Illuminate\Contracts\Console\Kernel::class);
$k->bootstrap();

$hotels = App\Models\Hotel::all();
foreach ($hotels as $h) {
    echo "ID: {$h->id} | Name: {$h->name} | Place: " . json_encode($h->place) . " | Addr: {$h->address}\n";
}
