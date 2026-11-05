<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::first();
\Illuminate\Support\Facades\Auth::login($user);

$req = new \Illuminate\Http\Request();
$controller = new App\Http\Controllers\ChartOfAccountsController();
try {
    $res = $controller->index($req);
    $html = $res->render();
    echo "SUCCESS! Rendered length: " . strlen($html) . " bytes\n";
    // Check if PKR balance appears anywhere in the rendered table
    if (strpos($html, 'Balance (PKR)') !== false) {
        echo "WARNING: 'Balance (PKR)' found in view!\n";
    } else {
        echo "CHECK OK: No 'Balance (PKR)' in table header!\n";
    }
    if (strpos($html, 'AL-AHBAB') !== false) {
        echo "CHECK OK: Supplier AL-AHBAB found in view!\n";
    } else {
        echo "WARNING: Supplier not found in view!\n";
    }
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo $e->getTraceAsString();
}
