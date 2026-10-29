<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    view()->share('errors', new \Illuminate\Support\ViewErrorBag());
    $user = \App\Models\User::first();
    if ($user) {
        \Illuminate\Support\Facades\Auth::login($user);
    }

    $banks = \App\Models\Bank::all();
    $expenses = \App\Models\Expense::all();
    $suppliers = \App\Models\Supplier::all();
    $clients = \App\Models\Client::all();
    $staffMembers = \App\Models\Staff::all();
    $cashAccounts = \App\Models\Account::all();
    $autoRef = 'DT-EXP-000001';
    $autoJvRef = 'DT-JV-000001';

    $viewCreate = view('expense_transaction.create', compact('banks', 'expenses', 'suppliers', 'clients', 'staffMembers', 'cashAccounts', 'autoRef', 'autoJvRef'))->render();
    echo "expense_transaction.create: " . strlen($viewCreate) . " bytes OK\n";

    $transaction = \App\Models\ExpenseTransaction::first();
    if ($transaction) {
        $relatedJvEntries = collect();
        $viewEdit = view('expense_transaction.edit', compact('transaction', 'banks', 'expenses', 'suppliers', 'clients', 'staffMembers', 'cashAccounts', 'relatedJvEntries'))->render();
        echo "expense_transaction.edit: " . strlen($viewEdit) . " bytes OK\n";
    }
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . " in " . $e->getFile() . " line " . $e->getLine() . "\n";
}
