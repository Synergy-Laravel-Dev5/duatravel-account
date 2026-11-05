<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$k = $app->make(Illuminate\Contracts\Console\Kernel::class);
$k->bootstrap();

$tables = Illuminate\Support\Facades\DB::select('SHOW TABLES');
foreach ($tables as $t) {
    foreach ((array)$t as $k => $v) {
        $cnt = Illuminate\Support\Facades\DB::table($v)->count();
        if ($cnt > 0) {
            echo "$v: $cnt rows\n";
        }
    }
}
