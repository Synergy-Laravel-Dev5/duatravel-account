<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\BankTransferController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

Auth::loginUsingId(1);

$ctrl = new BankTransferController();

// Test filter view
$filterView = $ctrl->reportFilter();
echo "Filter view status: " . ($filterView ? "OK" : "FAIL") . "\n";

// Test report view with no params (all)
$req = Request::create('/bank-transfer/report', 'GET', []);
$view = $ctrl->report($req);
$rendered = $view->render();
echo "Report view (all) rendered length: " . strlen($rendered) . "\n";

// Test report view with bank_id = 1 (Meezan)
$req2 = Request::create('/bank-transfer/report', 'GET', ['bank_id' => '1']);
$view2 = $ctrl->report($req2);
$rendered2 = $view2->render();
echo "Report view (Meezan) rendered length: " . strlen($rendered2) . "\n";
