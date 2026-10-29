<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

\Auth::loginUsingId(1);

echo "Testing all key views...\n";

$tests = [
    'dashboard' => fn() => app(\App\Http\Controllers\DashboardController::class)->index(request()),
    'meal-plans.index' => fn() => app(\App\Http\Controllers\MealPlanController::class)->index(),
    'reports.daily' => fn() => app(\App\Http\Controllers\DailyReportController::class)->index(request()),
    'booking.create' => fn() => app(\App\Http\Controllers\BookingController::class)->create(),
    'booking.edit' => fn() => app(\App\Http\Controllers\BookingController::class)->edit(\App\Models\Booking::first()),
];

foreach ($tests as $name => $fn) {
    try {
        $res = $fn();
        if ($res instanceof \Illuminate\View\View) {
            $html = $res->render();
            echo "✓ {$name}: Rendered successfully (" . strlen($html) . " bytes)\n";
        } else {
            echo "✓ {$name}: Response returned (" . get_class($res) . ")\n";
        }
    } catch (\Throwable $e) {
        echo "✗ {$name}: ERROR - " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
}
