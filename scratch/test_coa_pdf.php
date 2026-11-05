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
    $res = $controller->exportPdf($req);
    echo "SUCCESS! PDF response type: " . get_class($res) . "\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine() . "\n";
}
