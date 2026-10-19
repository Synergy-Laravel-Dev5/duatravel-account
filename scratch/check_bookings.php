<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$bookings = App\Models\Booking::with(['hotels', 'visas', 'transports'])->latest('id')->take(5)->get();

foreach ($bookings as $b) {
    echo "========================================\n";
    echo "Booking ID: {$b->id} | Ref: {$b->booking_reference} | Curr: {$b->currency} | Total: {$b->total_amount} | FlightCost: {$b->flight_cost} | FlightROE: {$b->flight_cost_roe} / {$b->flight_roe}\n";
    foreach ($b->hotels as $h) {
        echo "  Hotel: {$h->hotel_name} | Nights: {$h->no_of_nights} | Rooms: {$h->no_of_rooms} | cpn: {$h->cost_per_night} | cost_amt: {$h->cost_amount} | curr: {$h->currency} | cost_roe: {$h->cost_roe} | roe: {$h->roe}\n";
    }
    foreach ($b->transports as $t) {
        echo "  Transport: {$t->route} | cost_rate: {$t->cost_rate} | cost_amt: {$t->cost_amount} | curr: {$t->currency} | cost_roe: {$t->cost_roe}\n";
    }
    foreach ($b->visas as $v) {
        echo "  Visa: {$v->passport_number} | cost_rate: {$v->cost_rate} | cost_amt: {$v->cost_amount} | curr: {$v->currency} | cost_roe: {$v->cost_roe}\n";
    }
}
