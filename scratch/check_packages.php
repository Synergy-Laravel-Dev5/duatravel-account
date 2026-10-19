<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "packages columns:\n";
foreach(Illuminate\Support\Facades\DB::select('DESCRIBE packages') as $c) {
    echo "{$c->Field} ({$c->Type})\n";
}
echo "\npackage rows:\n";
print_r(Illuminate\Support\Facades\DB::table('packages')->take(3)->get());
