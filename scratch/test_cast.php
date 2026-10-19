<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$hotel = new App\Models\BookingHotel();
$hotel->person_rates = [
    ['name' => 'John', 'cost' => 100],
    ['name' => 'Jane', 'cost' => 150]
];
echo "Raw attribute in model: " . $hotel->getAttributes()['person_rates'] . "\n";
echo "Accessed via property: " . json_encode($hotel->person_rates) . "\n";
