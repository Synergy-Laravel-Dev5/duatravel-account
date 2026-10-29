<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;

echo "=== TABLES CHECK ===\n";
echo "hotels: " . implode(', ', Schema::getColumnListing('hotels')) . "\n\n";
echo "booking_hotels: " . implode(', ', Schema::getColumnListing('booking_hotels')) . "\n\n";
echo "bookings: " . implode(', ', Schema::getColumnListing('bookings')) . "\n\n";
echo "booking_people (or persons): ";
if (Schema::hasTable('booking_people')) {
    echo implode(', ', Schema::getColumnListing('booking_people')) . "\n\n";
} elseif (Schema::hasTable('booking_persons')) {
    echo implode(', ', Schema::getColumnListing('booking_persons')) . "\n\n";
} else {
    echo "NOT FOUND\n\n";
}

echo "meal_plans exists? " . (Schema::hasTable('meal_plans') ? 'YES' : 'NO') . "\n";
if (Schema::hasTable('meal_plans')) {
    echo "meal_plans cols: " . implode(', ', Schema::getColumnListing('meal_plans')) . "\n";
}

echo "\nDashboard routes / controller:\n";
foreach (\Route::getRoutes() as $route) {
    if (str_contains($route->uri(), 'dashboard') || str_contains($route->uri(), 'daily') || str_contains($route->uri(), 'reminder')) {
        echo $route->methods()[0] . ' ' . $route->uri() . ' -> ' . $route->getActionName() . "\n";
    }
}
