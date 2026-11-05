<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$k = $app->make(Illuminate\Contracts\Console\Kernel::class);
$k->bootstrap();

$places = App\Models\Hotel::select('place')->distinct()->get();
foreach ($places as $p) {
    $cnt = App\Models\Hotel::where('place', $p->place)->count();
    echo "Place: " . json_encode($p->place) . " | Count: $cnt\n";
}
