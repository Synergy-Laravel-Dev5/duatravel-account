<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$booking = \App\Models\Booking::with(['client', 'company', 'package', 'persons', 'hotels', 'transports', 'visas'])->find(1);
$selectedPerson = null;
$showRates = 0;
$currencyMode = 'mix';

$html = view('booking.umrah_invoice', compact('booking', 'selectedPerson', 'showRates', 'currencyMode'))->render();
echo "Rendered length: " . strlen($html) . "\n";

// Check if any old static text exists:
$patterns = ['7620', '579120', 'PK170', 'PK832', 'ANJUM', 'DALLAH', '0920', '1115', '1920', '0120', 'JED APT MAK HTL'];
foreach ($patterns as $p) {
    if (strpos($html, $p) !== false) {
        echo "WARNING: Found '$p' in rendered HTML!\n";
    }
}
echo "Static check completed.\n";
