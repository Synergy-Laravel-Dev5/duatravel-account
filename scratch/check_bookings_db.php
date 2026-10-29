<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$all = App\Models\Booking::withTrashed()->get();
echo "Total bookings in DB: " . $all->count() . "\n";
foreach ($all as $b) {
    echo "ID: {$b->id}, deleted_at: {$b->deleted_at}\n";
    if ($b->trashed()) {
        $b->restore();
        echo "Restored booking #{$b->id}\n";
    }
}
