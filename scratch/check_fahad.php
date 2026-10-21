<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$visas = App\Models\BookingVisa::all();
echo "Total Visas in DB: " . $visas->count() . "\n";
foreach ($visas as $v) {
    echo "Visa ID: {$v->id}, Booking ID: {$v->booking_id}, Pilgrim: {$v->given_name}, Passport: {$v->passport_number}, Booking Date: {$v->booking_date}, Option Date: {$v->option_date}, Created: {$v->created_at}\n";
}

$hotels = App\Models\BookingHotel::all();
echo "Total Hotels in DB: " . $hotels->count() . "\n";
foreach ($hotels as $h) {
    echo "Hotel ID: {$h->id}, Booking ID: {$h->booking_id}, Hotel: {$h->hotel_name}, CheckIn: {$h->check_in}, Booking Date: {$h->booking_date}, Option Date: {$h->option_date}\n";
}
