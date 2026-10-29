<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;

echo "=== TRANSACTIONS COLUMNS ===\n";
print_r(Schema::getColumnListing('transactions'));

echo "\n=== EXPENSE_TRANSACTIONS COLUMNS ===\n";
print_r(Schema::getColumnListing('expense_transactions'));
