<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;

echo "--- booking_persons columns ---\n";
print_r(Schema::getColumnListing('booking_persons'));

echo "\n--- bookings columns ---\n";
print_r(Schema::getColumnListing('bookings'));

echo "\n--- booking_hotels columns ---\n";
print_r(Schema::getColumnListing('booking_hotels'));

echo "\n--- booking_visas columns ---\n";
print_r(Schema::getColumnListing('booking_visas'));

echo "\n--- booking_transports columns ---\n";
print_r(Schema::getColumnListing('booking_transports'));
