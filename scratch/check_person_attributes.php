<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$b = App\Models\Booking::with('persons')->find(1);
foreach ($b->persons as $p) {
    print_r($p->toArray());
}
