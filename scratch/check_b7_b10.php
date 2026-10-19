<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

foreach ([7, 10] as $id) {
    $b = App\Models\Booking::with(['hotels', 'visas', 'transports', 'persons'])->find($id);
    if (!$b) {
        echo "Booking $id not found\n";
        continue;
    }
    echo "================ BOOKING $id ================\n";
    echo "Client: " . ($b->client ? $b->client->name : 'N/A') . "\n";
    echo "no_of_pax: {$b->no_of_pax}, adults: {$b->adults_count}, children: {$b->children_count}, infants: {$b->infants_count}\n";
    echo "flight_charges: {$b->flight_charges}, hotel_charges: {$b->hotel_charges}, visa_charges: {$b->visa_charges}, transport_charges: {$b->transport_charges}\n";
    echo "total_amount: {$b->total_amount}, total_received: {$b->total_received}, balance: {$b->balance}\n";
    echo "flight_currency: {$b->flight_currency}, flight_cost_roe: {$b->flight_cost_roe}, flight_sale_roe: {$b->flight_sale_roe}\n";
    echo "flight_adult_cost: {$b->flight_adult_cost}, flight_adult_sell: {$b->flight_adult_sell}\n";
    echo "Hotels count: " . $b->hotels->count() . "\n";
    foreach ($b->hotels as $h) {
        echo "  Hotel: {$h->hotel_name} | Nights: {$h->no_of_nights} | Rooms: {$h->no_of_rooms} | cpn: {$h->cost_per_night} | spn: {$h->selling_per_night} | cost_amt: {$h->cost_amount} | sell_amt: {$h->selling_amount} | curr: {$h->currency} | cost_roe: {$h->cost_roe} | sale_roe: {$h->sale_roe}\n";
    }
    echo "Persons count: " . $b->persons->count() . "\n";
    foreach ($b->persons as $p) {
        echo "  Person: {$p->full_name} | haji_id: {$p->haji_id}\n";
    }
}
