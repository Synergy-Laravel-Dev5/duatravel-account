<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Client;

$client = Client::first();
if ($client) {
    $client->update([
        'opening_balance_pkr' => 15000,
        'opening_balance_sar' => 200,
        'opening_balance_pkr_type' => 'debit',
        'opening_balance_sar_type' => 'debit',
    ]);
    echo "Client {$client->id} updated with PKR: {$client->opening_balance_pkr}, SAR: {$client->opening_balance_sar}\n";
}
