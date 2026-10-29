<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Bank;
use App\Models\Account;
use App\Models\Client;
use App\Models\Supplier;

$banks = Bank::where('status', 'active')->orderBy('name')->get();
$cashAccounts = Account::where('status', 'active')->where('account_type', 'cash')->orderBy('account_name')->get();
$clients = Client::where('status', 'active')->orWhereNull('status')->orderBy('name')->get();
$suppliers = Supplier::where('status', 'active')->orWhereNull('status')->orderBy('name')->get();

echo "Banks: " . $banks->count() . "\n";
echo "Cash Accounts: " . $cashAccounts->count() . "\n";
echo "Clients: " . $clients->count() . "\n";
echo "Suppliers: " . $suppliers->count() . "\n";
