<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$booking = App\Models\Booking::with(['persons', 'hotels', 'visas', 'transports'])->latest()->first();
if ($booking) {
    echo "Booking ID: " . $booking->id . "\n";
    echo "No of Pax: " . $booking->no_of_pax . "\n";
    echo "Persons count: " . $booking->persons->count() . "\n";
    echo "Hotels count: " . $booking->hotels->count() . "\n";
    echo "Visas count: " . $booking->visas->count() . "\n";
    echo "Transports count: " . $booking->transports->count() . "\n";
    foreach ($booking->persons as $p) {
        echo " - Person: id=" . $p->id . ", name=" . $p->full_name . ", pax_type=" . $p->pax_type . "\n";
    }
} else {
    echo "No booking found\n";
}
