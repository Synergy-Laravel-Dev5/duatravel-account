<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
$cols = DB::select("SHOW COLUMNS FROM accounts");
foreach($cols as $c) {
    echo "{$c->Field} | {$c->Type} | {$c->Null} | {$c->Default}\n";
}
