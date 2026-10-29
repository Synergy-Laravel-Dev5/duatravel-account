<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cols = Illuminate\Support\Facades\Schema::getColumnListing('expense_transactions');
echo "expense_transactions columns: " . implode(', ', $cols) . "\n";

$accCols = Illuminate\Support\Facades\Schema::getColumnListing('accounts');
echo "accounts columns: " . implode(', ', $accCols) . "\n";
