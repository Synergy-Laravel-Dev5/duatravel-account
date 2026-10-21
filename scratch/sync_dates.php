<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$bookings = App\Models\Booking::with(['visas', 'hotels', 'transports'])->get();
foreach ($bookings as $b) {
    $vDate = $b->visas->whereNotNull('booking_date')->first()?->booking_date;
    $hDate = $b->hotels->whereNotNull('booking_date')->first()?->booking_date;
    $tDate = $b->transports->whereNotNull('booking_date')->first()?->booking_date;
    $serviceDate = $vDate ?: ($hDate ?: $tDate);
    if ($serviceDate && $b->booking_date !== $serviceDate) {
        echo "Updating Booking {$b->id}: old date {$b->booking_date} -> new date {$serviceDate}\n";
        $b->booking_date = $serviceDate;
        $b->saveQuietly();
    }
}
echo "Done syncing booking dates!\n";
