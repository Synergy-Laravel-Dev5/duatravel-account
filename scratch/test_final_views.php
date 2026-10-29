<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Http\Controllers\BankController;
use App\Http\Controllers\BankTransferController;

// Mock login user
$u = User::first();
if ($u) {
    Auth::login($u);
}

echo "1. Testing BankController@index view rendering...\n";
$bankCtrl = new BankController();
$viewBankIndex = $bankCtrl->index();
$html1 = $viewBankIndex->render();
echo "Bank Index rendered successfully! HTML length: " . strlen($html1) . "\n";

echo "2. Testing BankTransferController@report (Consolidated)...\n";
$btCtrl = new BankTransferController();
$reqAll = new Request();
$viewBtReport = $btCtrl->report($reqAll);
$html2 = $viewBtReport->render();
echo "Bank Transfer Report (All) rendered successfully! HTML length: " . strlen($html2) . "\n";

echo "3. Testing BankTransferController@report (Samba Bank ID 1)...\n";
$reqSamba = new Request(['bank_id' => 1]);
$viewSamba = $btCtrl->report($reqSamba);
$html3 = $viewSamba->render();
echo "Bank Transfer Report (Samba) rendered successfully! HTML length: " . strlen($html3) . "\n";

echo "4. Testing BankTransferController@reportPdf (Samba Bank ID 1)...\n";
$pdfResponse = $btCtrl->reportPdf($reqSamba);
echo "PDF download generated successfully! Type: " . get_class($pdfResponse) . "\n";

echo "\n--- ALL CHECKS PASSED 100% PERFECTLY! ---\n";
