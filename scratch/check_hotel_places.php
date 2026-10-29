<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Hotel;

foreach (Hotel::all() as $h) {
    $p = $h->place ? (is_object($h->place) ? $h->place->value : $h->place) : 'None';
    echo "ID: {$h->id} | Name: {$h->name} | Place: {$p}\n";
}
