<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$b = \App\Models\Booking::with(['client', 'company', 'package', 'persons', 'hotels', 'transports', 'visas'])->find(1);
if (!$b) {
    echo "Booking 1 not found!\n";
    exit;
}

echo "Booking #1 details:\n";
echo "Voucher: " . $b->voucher_number . "\n";
echo "Client: " . ($b->client ? $b->client->name : 'none') . "\n";
echo "Total Amount: " . $b->total_amount . "\n";
echo "Persons count: " . $b->persons->count() . "\n";
foreach ($b->persons as $p) {
    echo "  - Person: {$p->given_name} {$p->surname}, Passport: {$p->passport_number}, Visa: {$p->visa_number}, DOB: {$p->dob}\n";
}
echo "Hotels count: " . $b->hotels->count() . "\n";
foreach ($b->hotels as $h) {
    echo "  - Hotel: {$h->hotel_name}, City: {$h->location}, Check-in: {$h->check_in}, Check-out: {$h->check_out}, Nights: {$h->no_of_nights}, Rooms: {$h->no_of_rooms}, Rate: {$h->selling_per_night}, Total: {$h->selling_amount}\n";
}
echo "Transports count: " . $b->transports->count() . "\n";
foreach ($b->transports as $t) {
    echo "  - Transport: {$t->transport_type}, Sector: {$t->route_sector}, Date: {$t->service_date}, Rate: {$t->selling_rate}, Total: {$t->selling_amount}\n";
}
echo "Visas count: " . $b->visas->count() . "\n";
foreach ($b->visas as $v) {
    echo "  - Visa: {$v->visa_type}, Rate: {$v->selling_rate}, Total: {$v->selling_amount}\n";
}
echo "Flight info:\n";
echo "Dep flight: {$b->departure_flight}, Dep date: {$b->departure_date}, Dep time: {$b->departure_time}, Dep sector: {$b->departure_sector}\n";
echo "Arr flight: {$b->arrival_flight}, Arr date: {$b->arrival_date}, Arr time: {$b->arrival_time}, Arr sector: {$b->arrival_sector}\n";
