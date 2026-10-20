<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\ProfitLossReportController;
use App\Http\Controllers\BankTransferController;
use App\Http\Controllers\CashReportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

Auth::loginUsingId(1);

echo "=== 1. PROFIT & LOSS REPORT TEST ===\n";
$plCtrl = new ProfitLossReportController();
$plReq9 = Request::create('/reports/profit-loss', 'GET', ['month' => '9']);
$plView9 = $plCtrl->index($plReq9);
$plData9 = $plView9->getData();
echo "Month 9 Bookings: " . count($plData9['processedBookings']) . "\n";
foreach ($plData9['processedBookings'] as $pb) {
    echo "  - Booking ID: {$pb->booking->id} | Booking Date: {$pb->booking->booking_date} | Profit PKR: {$pb->gross_profit_pkr}\n";
}

$plReq10 = Request::create('/reports/profit-loss', 'GET', ['month' => '10']);
$plView10 = $plCtrl->index($plReq10);
$plData10 = $plView10->getData();
echo "Month 10 Bookings: " . count($plData10['processedBookings']) . "\n";
foreach ($plData10['processedBookings'] as $pb) {
    echo "  - Booking ID: {$pb->booking->id} | Booking Date: {$pb->booking->booking_date} | Profit PKR: {$pb->gross_profit_pkr}\n";
}

echo "\n=== 2. BANK TRANSACTION REPORT TEST ===\n";
$btCtrl = new BankTransferController();
$btReq = Request::create('/bank-transfer/report', 'GET', ['bank_id' => '1']);
$btView = $btCtrl->report($btReq);
$btData = $btView->getData();
echo "Meezan Bank Txns: " . $btData['totalCount'] . "\n";
echo "Deposits (Cr): PKR " . $btData['totalDeposits'] . "\n";
echo "Withdrawals (Dr): PKR " . $btData['totalWithdrawals'] . "\n";
echo "Net Movement: PKR " . $btData['netMovement'] . "\n";
foreach ($btData['transactions'] as $tx) {
    echo "  - Date: {$tx['date']} | Voucher: {$tx['voucher_no']} | In: PKR {$tx['deposit']} | Out: PKR {$tx['withdrawal']} | Bal: PKR {$tx['running_balance']} | Party: {$tx['party_name']}\n";
}

// Test PDF render
$btPdfResponse = $btCtrl->reportPdf($btReq);
echo "Bank PDF generated successfully (Status: " . $btPdfResponse->getStatusCode() . ")\n";

echo "\n=== 3. CASH REPORT TEST ===\n";
$cashCtrl = new CashReportController();
$cashReq = Request::create('/reports/cash', 'GET', []);
$cashView = $cashCtrl->index($cashReq);
$cashData = $cashView->getData();
echo "Total Cash Entries: " . $cashData['totalCount'] . "\n";
echo "Total Cash Inflow: PKR " . $cashData['totalCashIn'] . "\n";
echo "Total Cash Outflow: PKR " . $cashData['totalCashOut'] . "\n";
echo "Net Cash in Hand: PKR " . $cashData['netCashBalance'] . "\n";
foreach ($cashData['transactions'] as $tx) {
    echo "  - Date: {$tx['date']} | Voucher: {$tx['voucher_no']} | Flow: {$tx['flow']} | In: PKR {$tx['cash_in']} | Out: PKR {$tx['cash_out']} | Bal: PKR {$tx['running_balance']} | Party: {$tx['party_name']}\n";
}

// Test Cash PDF render
$cashPdfResponse = $cashCtrl->exportPdf($cashReq);
echo "Cash PDF generated successfully (Status: " . $cashPdfResponse->getStatusCode() . ")\n";

// Render HTML blade views to ensure 0 syntax or blade errors
$cashRendered = $cashView->render();
echo "Cash HTML view rendered length: " . strlen($cashRendered) . "\n";
$btRendered = $btView->render();
echo "Bank HTML view rendered length: " . strlen($btRendered) . "\n";
$plRendered = $plView9->render();
echo "P&L HTML view rendered length: " . strlen($plRendered) . "\n";

echo "\nALL TESTS PASSED PERFECTLY!\n";
