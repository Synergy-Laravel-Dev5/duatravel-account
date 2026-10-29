<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$accounts = App\Models\Account::all();
echo "Accounts count: " . $accounts->count() . "\n";
foreach($accounts as $acc) {
    echo "ID: {$acc->id} | Name: {$acc->account_name} | Type: {$acc->account_type} | PKR OB: {$acc->opening_balance_pkr} | SAR OB: {$acc->opening_balance_sar} | Status: {$acc->status}\n";
}
