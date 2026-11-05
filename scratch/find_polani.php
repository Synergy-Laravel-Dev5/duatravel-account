<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

foreach (['suppliers', 'airlines', 'clients', 'companies', 'users'] as $table) {
    $rows = DB::table($table)->where('name', 'LIKE', '%Polani%')->get();
    if ($rows->count() > 0) {
        echo "Found in $table: " . json_encode($rows) . PHP_EOL;
    }
}
