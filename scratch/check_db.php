<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Transactions columns:\n";
print_r(\Illuminate\Support\Facades\Schema::getColumnListing('transactions'));

echo "\nExpenseTransactions columns:\n";
print_r(\Illuminate\Support\Facades\Schema::getColumnListing('expense_transactions'));

echo "\nAccounts columns:\n";
print_r(\Illuminate\Support\Facades\Schema::getColumnListing('accounts'));
