<?php
$create = file_get_contents('resources/views/booking/create.blade.php');
$lines = explode("\n", $create);
foreach ($lines as $i => $line) {
    if (strpos($line, 'hotelAccommodationSummaryRows') !== false || strpos($line, 'addNewHotel') !== false) {
        echo ($i + 1) . ': ' . trim($line) . "\n";
    }
}
