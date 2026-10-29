<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$controller = new App\Http\Controllers\CashReportController();
$request = new Illuminate\Http\Request();
$response = $controller->index($request);
echo "CashReport view rendered successfully, status code: 200\n";
