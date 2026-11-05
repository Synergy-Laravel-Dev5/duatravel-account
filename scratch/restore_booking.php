<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$k = $app->make(Illuminate\Contracts\Console\Kernel::class);
$k->bootstrap();

$b = App\Models\Booking::withTrashed()->first();
if ($b) {
    $b->restore();
    echo "Restored booking ID: " . $b->id . "\n";
}
