<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$clientsUpdated = App\Models\Client::where('opening_balance_roe', '<=', 1)
    ->where('opening_balance_sar', '>', 0)
    ->update(['opening_balance_sar' => 0]);
echo "Clients updated to 0 SAR: {$clientsUpdated}\n";

$suppliersUpdated = App\Models\Supplier::where('opening_balance_roe', '<=', 1)
    ->where('opening_balance_sar', '>', 0)
    ->update(['opening_balance_sar' => 0]);
echo "Suppliers updated to 0 SAR: {$suppliersUpdated}\n";
