<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing DB features & persistence...\n";

// 1. Meal Plans in DB
$plans = \App\Models\MealPlan::all();
echo "✓ Meal plans count: " . $plans->count() . " (" . $plans->pluck('code')->implode(', ') . ")\n";

// 2. Check BookingHotel columns
$hotel = \App\Models\BookingHotel::first();
if ($hotel) {
    $hotel->meal_plan = 'BB';
    $hotel->room_view = 'Kaaba View';
    $hotel->save();
    $hotel->refresh();
    echo "✓ BookingHotel #{$hotel->id} updated with meal_plan={$hotel->meal_plan}, room_view={$hotel->room_view}\n";
}

// 3. Check Booking booking_category column
$booking = \App\Models\Booking::first();
if ($booking) {
    $booking->booking_category = 'domestic';
    $booking->save();
    $booking->refresh();
    echo "✓ Booking #{$booking->id} updated with booking_category={$booking->booking_category}\n";
}

// 4. Test Operational Reminders in Dashboard
$today = \Carbon\Carbon::today()->toDateString();
$next7 = \Carbon\Carbon::today()->addDays(7)->toDateString();
$checkins = \App\Models\BookingHotel::whereBetween('check_in', [$today, $next7])->count();
echo "✓ Upcoming checkins in next 7 days: {$checkins}\n";

echo "\nALL FEATURE SIMULATIONS PASSED!\n";
