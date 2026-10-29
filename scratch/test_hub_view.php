<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Http\Controllers\FinancialReportHubController;

$u = User::first();
if ($u) Auth::login($u);

$hubCtrl = new FinancialReportHubController();

foreach (['bank', 'cash', 'client', 'supplier'] as $tab) {
    echo "Testing FinancialReportHub tab [{$tab}]...\n";
    $req = new Request(['tab' => $tab]);
    $view = $hubCtrl->index($req);
    $html = $view->render();
    echo "Tab [{$tab}] rendered successfully! HTML length: " . strlen($html) . "\n";
}

echo "\n--- ALL 4 TABS RENDERED 100% PERFECTLY! ---\n";
