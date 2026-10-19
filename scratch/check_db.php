<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "RoomTypes: " . App\Models\RoomType::count() . "\n";
print_r(App\Models\RoomType::all()->pluck('name', 'id')->toArray());

echo "Vehicles: " . App\Models\Vehicle::count() . "\n";
print_r(App\Models\Vehicle::all()->pluck('vehicle_type', 'id')->toArray());

echo "Airlines: " . App\Models\Airline::count() . "\n";
echo "Routes: " . App\Models\TravelRoute::count() . "\n";
