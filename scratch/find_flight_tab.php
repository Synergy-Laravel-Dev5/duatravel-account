<?php
$create = file_get_contents('resources/views/booking/create.blade.php');
$lines = explode("\n", $create);
foreach ($lines as $i => $line) {
    if (strpos($line, 'flight_persons') !== false || strpos($line, 'flight-passenger-table') !== false || strpos($line, 'id="flight"') !== false) {
        echo ($i + 1) . ': ' . trim($line) . "\n";
    }
}
