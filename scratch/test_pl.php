<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$controller = new App\Http\Controllers\ProfitLossReportController();
$request = new Illuminate\Http\Request();
$response = $controller->index($request);
$data = $response->getData();

echo "Processed bookings count: " . count($data['processedBookings']) . "\n";
foreach ($data['processedBookings'] as $b) {
    if (in_array($b->booking->id, [10, 9, 8, 7])) {
        echo "----------------------------------------\n";
        echo "Booking ID: {$b->booking->id}\n";
        echo "  Pax: {$b->pax}\n";
        echo "  Selling: PKR " . number_format($b->selling_pkr, 2) . "\n";
        echo "  Hotel Cost: PKR " . number_format($b->hotel_cost_pkr, 2) . "\n";
        echo "  Flight Cost: PKR " . number_format($b->flight_cost_pkr, 2) . "\n";
        echo "  Visa Cost: PKR " . number_format($b->visa_cost_pkr, 2) . "\n";
        echo "  Transport Cost: PKR " . number_format($b->transport_cost_pkr, 2) . "\n";
        echo "  Total Direct Cost: PKR " . number_format($b->direct_cost_pkr, 2) . "\n";
        echo "  Gross Profit: PKR " . number_format($b->gross_profit_pkr, 2) . " ({$b->margin_pct}%)\n";
        echo "  Per Pax Profit: PKR " . number_format($b->per_pax_profit, 2) . "\n";
    }
}
