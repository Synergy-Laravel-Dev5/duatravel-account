<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$controller = new App\Http\Controllers\ExpenseTransactionController();
$view = $controller->create();
echo "expense_transaction.create rendered successfully, status 200!\n";
