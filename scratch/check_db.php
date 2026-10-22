<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "--- CLIENTS ---\n";
foreach (\App\Models\Client::all() as $c) {
    echo "ID: {$c->id} | Name: {$c->name} | PackageType: {$c->package_type}\n";
}

echo "\n--- EXPENSE TRANSACTIONS ---\n";
foreach (\App\Models\ExpenseTransaction::latest()->take(10)->get() as $et) {
    echo "ID: {$et->id} | PayeeType: {$et->payee_type} | Season: {$et->hajj_umrah} | Amount: {$et->amount} | Type: {$et->transaction_type}\n";
}
