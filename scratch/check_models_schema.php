<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$bp = \App\Models\BookingPerson::first();
echo "BookingPerson attributes:\n";
print_r(array_keys($bp ? $bp->getAttributes() : []));

$bv = \App\Models\BookingVisa::first();
echo "\nBookingVisa attributes:\n";
print_r(array_keys($bv ? $bv->getAttributes() : []));

$bh = \App\Models\BookingHotel::first();
echo "\nBookingHotel attributes:\n";
print_r(array_keys($bh ? $bh->getAttributes() : []));

$bt = \App\Models\BookingTransport::first();
echo "\nBookingTransport attributes:\n";
print_r(array_keys($bt ? $bt->getAttributes() : []));
