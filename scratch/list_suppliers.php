<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "SUPPLIERS:\n";
foreach (App\Models\Supplier::withTrashed()->get() as $s) {
    echo $s->id . " | " . $s->name . " | deleted: " . ($s->deleted_at ?? 'null') . "\n";
}

echo "\nACCOUNTS:\n";
foreach (App\Models\Account::all() as $a) {
    echo $a->id . " | " . $a->account_code . " | " . $a->account_name . " | " . $a->account_type . "\n";
}
