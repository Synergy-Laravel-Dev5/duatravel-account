<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Expense;
use App\Models\ExpenseTransaction;
use App\Http\Controllers\ProfitLossReportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

Auth::login(User::first());

// 1. Create a test expense head
$expHead = Expense::firstOrCreate(['name' => 'Office Tea & Refreshment']);

// 2. Create a test transaction for it
$t = ExpenseTransaction::create([
    'expense_id'       => $expHead->id,
    'hajj_umrah'       => 'umrah',
    'year'             => 2026,
    'amount'           => 4500,
    'payment_type'     => 'cash',
    'transaction_type' => 'debit',
    'payee_type'       => 'expense',
    'currency'         => 'PKR',
    'exchange_rate'    => 1,
    'date'             => '2026-10-29',
    'description'      => 'Office refreshment purchase',
]);

// 3. Render report statement
$controller = new ProfitLossReportController();
$req = new Request(['package_type' => 'all']);
$response = $controller->index($req);

$html = $response->render();
if (preg_match('/<table class="table table-bordered table-sm align-middle fs-13 mb-0 statement-table"[^>]*>(.*?)<\/table>/s', $html, $matches)) {
    $lines = explode("\n", strip_tags($matches[0]));
    foreach ($lines as $line) {
        $trimmed = trim($line);
        if (!empty($trimmed)) {
            echo $trimmed . "\n";
        }
    }
}

// Clean up
$t->forceDelete();
$expHead->delete();
echo "\nTest cleanup done.\n";
