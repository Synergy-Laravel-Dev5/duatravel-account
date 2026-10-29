<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Hotel;
use App\Models\Booking;

echo "=== HOTELS IN DB ===\n";
foreach (Hotel::all() as $h) {
    echo "ID: {$h->id} | Name: {$h->name} | Place: '{$h->place}' | Type: '{$h->accommodation_type}'\n";
}

echo "\n=== BOOKING 1 FLIGHT & TIME FIELDS ===\n";
$b = Booking::find(1);
if ($b) {
    echo "departure_date: {$b->departure_date}\n";
    echo "departure_time: {$b->departure_time}\n";
    echo "arrival_date: {$b->arrival_date}\n";
    echo "arrival_time: {$b->arrival_time}\n";
    echo "flight_time_format: {$b->flight_time_format}\n";
    echo "flight_booking_date: {$b->flight_booking_date}\n";
    echo "flight_option_date: {$b->flight_option_date}\n";
}

echo "\n=== DASHBOARD VIEW & CONTROLLER ===\n";
$dashContent = file_get_contents('app/Http/Controllers/DashboardController.php');
echo substr($dashContent, 0, 800) . "\n";
