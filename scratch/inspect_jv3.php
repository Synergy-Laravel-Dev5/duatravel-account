<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$txs = App\Models\ExpenseTransaction::where('reference_no', 'like', '%DT-JV-000003%')
    ->orWhere('id', 3)
    ->get();

foreach ($txs as $t) {
    print_r($t->toArray());
}
