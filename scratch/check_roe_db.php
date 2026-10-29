<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$clients = App\Models\Client::where('opening_balance_roe', '<=', 1)->get(['id', 'name', 'opening_balance_pkr', 'opening_balance_sar', 'opening_balance_roe']);
echo "Clients with ROE <= 1:\n";
foreach ($clients as $c) {
    echo "ID: {$c->id}, Name: {$c->name}, PKR: {$c->opening_balance_pkr}, SAR: {$c->opening_balance_sar}, ROE: {$c->opening_balance_roe}\n";
}

$suppliers = App\Models\Supplier::where('opening_balance_roe', '<=', 1)->get(['id', 'name', 'opening_balance_pkr', 'opening_balance_sar', 'opening_balance_roe']);
echo "\nSuppliers with ROE <= 1:\n";
foreach ($suppliers as $s) {
    echo "ID: {$s->id}, Name: {$s->name}, PKR: {$s->opening_balance_pkr}, SAR: {$s->opening_balance_sar}, ROE: {$s->opening_balance_roe}\n";
}
