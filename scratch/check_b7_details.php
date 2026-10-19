<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$b = App\Models\Booking::with(['visas', 'transports', 'hotels', 'persons'])->find(7);
echo "Booking 7 Visas count: " . $b->visas->count() . "\n";
foreach ($b->visas as $v) {
    echo "  Visa: rate={$v->cost_rate}, sell={$v->selling_rate}, curr={$v->currency}, amt={$v->selling_amount}\n";
}
echo "Package cost: {$b->package_cost}, other: {$b->other_charges}, addl: {$b->additional_services_amount}\n";
