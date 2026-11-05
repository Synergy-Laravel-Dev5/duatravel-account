<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cols = DB::select("DESCRIBE booking_visas");
foreach ($cols as $c) {
    echo "{$c->Field} | {$c->Type} | Null: {$c->Null} | Default: " . json_encode($c->Default) . PHP_EOL;
}
