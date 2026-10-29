<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\SupplierController;

$u = User::first();
if ($u) Auth::login($u);

$txCtrl = new TransactionController();
$req2 = new Request(['client_id' => 1]);
$viewCli = $txCtrl->ledgerView($req2);
$htmlCli = $viewCli->render();
echo "Client Ledger rendered successfully! Length: " . strlen($htmlCli) . "\n";
echo "Done!\n";
