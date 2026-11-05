<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$b = App\Models\Booking::with(['persons', 'visas', 'hotels', 'transports'])->latest('id')->first();
if ($b) {
    echo "ID: " . $b->id . "\n";
    echo "booking_no: " . $b->booking_no . "\n";
    echo "no_of_pax: " . $b->no_of_pax . "\n";
    echo "persons count: " . $b->persons->count() . "\n";
    echo "visas count: " . $b->visas->count() . "\n";
    echo "hotels count: " . $b->hotels->count() . "\n";
    echo "transports count: " . $b->transports->count() . "\n";
} else {
    echo "No booking found\n";
}
