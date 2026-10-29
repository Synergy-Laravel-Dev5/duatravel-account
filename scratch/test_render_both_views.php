<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

view()->share('errors', new Illuminate\Support\ViewErrorBag());
$user = App\Models\User::first();
if ($user) {
    Illuminate\Support\Facades\Auth::login($user);
}

$controller = new App\Http\Controllers\ExpenseTransactionController();

// 1. Test create view
$viewCreate = $controller->create();
$renderedCreate = $viewCreate->render();
echo "expense_transaction.create rendered successfully! Length: " . strlen($renderedCreate) . "\n";

// 2. Test edit view
$txn = App\Models\ExpenseTransaction::first();
if ($txn) {
    $viewEdit = $controller->edit($txn->id);
    $renderedEdit = $viewEdit->render();
    echo "expense_transaction.edit rendered successfully! Length: " . strlen($renderedEdit) . "\n";
} else {
    echo "No expense transaction found to test edit view.\n";
}
