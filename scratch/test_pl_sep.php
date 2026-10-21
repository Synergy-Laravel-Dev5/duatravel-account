<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$request = Illuminate\Http\Request::create('/reports/profit-loss', 'GET', ['month' => '9', 'year' => 'all']);
$ctrl = new App\Http\Controllers\ProfitLossReportController();
$view = $ctrl->index($request);
$data = $view->getData();
echo "Total Bookings in Sep PnL: " . count($data['processedBookings']) . "\n";
foreach ($data['processedBookings'] as $pb) {
    echo "  - Booking #{$pb->booking->id}: Date: {$pb->booking->booking_date}, Client: " . ($pb->booking->client->name ?? '-') . ", Gross Profit: {$pb->gross_profit_pkr}\n";
}
