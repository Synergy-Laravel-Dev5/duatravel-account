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
file_put_contents('scratch/rendered_invoice_1.html', $html);
echo "Rendered invoice length: " . strlen($html) . "\n";

// Let's inspect flight section in rendered html
preg_match('/<!-- FLIGHT DETAIL SECTION -->(.*?)<!-- HOTEL/s', $html, $m_flight);
if (isset($m_flight[1])) {
    echo "=== Flight Section in HTML ===\n";
    echo strip_tags($m_flight[1]) . "\n";
}

// Let's inspect hotel section in rendered html
preg_match('/<!-- HOTEL \| ACCOMODATION SECTION -->(.*?)<!-- TRANSPORT/s', $html, $m_hotel);
if (isset($m_hotel[1])) {
    echo "=== Hotel Section in HTML ===\n";
    echo strip_tags($m_hotel[1]) . "\n";
}

// Let's inspect transport section in rendered html
preg_match('/<!-- TRANSPORT SECTION -->(.*?)<!-- BOTTOM/s', $html, $m_tr);
if (isset($m_tr[1])) {
    echo "=== Transport Section in HTML ===\n";
    echo strip_tags($m_tr[1]) . "\n";
}
