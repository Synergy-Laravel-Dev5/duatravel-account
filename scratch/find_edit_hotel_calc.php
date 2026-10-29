<?php
$lines = file('resources/views/booking/edit.blade.php');
foreach ($lines as $i => $line) {
    if (strpos($line, 'calculateHotel') !== false || strpos($line, 'hotel-sell-total') !== false || strpos($line, 'hotel_charges') !== false) {
        echo "Line " . ($i + 1) . ": " . trim($line) . "\n";
    }
}
