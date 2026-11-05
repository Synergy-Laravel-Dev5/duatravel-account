<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = DB::select('SHOW TABLES');
foreach ($tables as $t) {
    $tbl = array_values((array)$t)[0];
    try {
        $cols = DB::getSchemaBuilder()->getColumnListing($tbl);
        foreach ($cols as $col) {
            $count = DB::table($tbl)->where($col, 'LIKE', '%Polani%')->count();
            if ($count > 0) {
                echo "Found $count in table `$tbl`, column `$col`\n";
            }
        }
    } catch (\Throwable $e) {}
}
