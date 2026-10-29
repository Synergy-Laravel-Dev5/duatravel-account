<?php
$create = file_get_contents('resources/views/booking/create.blade.php');
$lines = explode("\n", $create);
foreach ($lines as $i => $line) {
    if (strpos($line, 'package_type') !== false || strpos($line, 'id="booking-details"') !== false || strpos($line, 'id="details"') !== false) {
        echo ($i + 1) . ': ' . trim($line) . "\n";
    }
}
