<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\ProfitLossReportController;
use Illuminate\Http\Request;

$ctrl = new ProfitLossReportController();
$req = Request::create('/reports/profit-loss', 'GET', [
    'year' => 'all',
    'month' => '10',
]);

$response = $ctrl->index($req);
$data = $response->getData();

echo "Processed bookings for Month = 10: " . count($data['processedBookings']) . "\n";
foreach($data['processedBookings'] as $pb) {
    echo "ID: {$pb->booking->id} | Booking Date: {$pb->booking->booking_date} | created_at: {$pb->booking->created_at} | Gross Profit: {$pb->gross_profit_pkr}\n";
}
echo "\nMonthly data keys:\n";
print_r(array_keys($data['monthlyData']));
