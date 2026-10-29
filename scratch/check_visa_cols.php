<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$cols = DB::select('DESCRIBE booking_visas');
foreach ($cols as $c) {
    echo "{$c->Field} | {$c->Type} | Null: {$c->Null} | Default: {$c->Default}\n";
}
