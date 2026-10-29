<?php
$edit = file_get_contents('resources/views/booking/edit.blade.php');
$lines = explode("\n", $edit);
foreach ($lines as $i => $line) {
    if (strpos($line, 'hotel-block') !== false || strpos($line, 'hotelAccommodationSummaryBox') !== false) {
        echo ($i + 1) . ': ' . trim($line) . "\n";
    }
}
