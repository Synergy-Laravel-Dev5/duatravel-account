<?php
$lines = file('resources/views/booking/create.blade.php');
foreach ($lines as $i => $line) {
    if (strpos($line, 'flight_time_format') !== false) {
        echo "Line " . ($i + 1) . ": " . trim($line) . "\n";
    }
}
