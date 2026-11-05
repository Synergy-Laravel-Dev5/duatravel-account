<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = \Illuminate\Support\Facades\DB::select('SHOW TABLES');
$dbName = \Illuminate\Support\Facades\DB::getDatabaseName();
$prop = "Tables_in_" . $dbName;
foreach ($tables as $t) {
    $tName = $t->$prop;
    try {
        $rows = \Illuminate\Support\Facades\DB::table($tName)->get();
        foreach ($rows as $r) {
            $json = json_encode($r);
            if (stripos($json, 'polani') !== false) {
                echo "Found in $tName:\n$json\n";
            }
        }
    } catch (\Throwable $e) {}
}
