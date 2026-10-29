<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Laravel booted successfully.\n";

try {
    $views = [
        'booking.create',
        'booking.edit',
        'client.create',
        'client.edit',
        'client.index',
        'suppliers.create',
        'suppliers.edit',
        'bank.create',
        'bank.edit',
        'account.index',
        'account.create',
        'account.edit',
        'transaction.create',
        'transaction.edit',
        'transaction.ledger',
        'package.create',
        'package.edit',
    ];

    $blade = $app->make('blade.compiler');
    foreach ($views as $view) {
        $path = $app->make('view')->getFinder()->find($view);
        echo "Compiling $view ($path)... ";
        $compiled = $blade->compileString(file_get_contents($path));
        echo "OK\n";
    }

    echo "All Blade views compiled cleanly without syntax errors!\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
