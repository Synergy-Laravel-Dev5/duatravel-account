<?php
$create = file_get_contents('resources/views/booking/create.blade.php');
$lines = explode("\n", $create);
foreach ($lines as $i => $line) {
    if (strpos($line, 'flight_time_format') !== false || strpos($line, 'departure_time') !== false || strpos($line, 'arrival_time') !== false) {
        echo ($i + 1) . ': ' . trim($line) . "\n";
    }
}
