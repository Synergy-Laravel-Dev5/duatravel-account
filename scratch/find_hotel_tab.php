<?php
$create = file_get_contents('resources/views/booking/create.blade.php');
$lines = explode("\n", $create);
foreach ($lines as $i => $line) {
    if (strpos($line, 'Hotel | Accomodation') !== false || strpos($line, 'Total Hotel Cost') !== false || strpos($line, 'id="hotel"') !== false || strpos($line, 'hotels-container') !== false) {
        echo ($i + 1) . ': ' . trim($line) . "\n";
    }
}
