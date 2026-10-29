<?php
$lines = file('resources/views/booking/create.blade.php');
foreach ($lines as $i => $line) {
    if (strpos($line, 'function rebuildPersons') !== false || strpos($line, 'function syncFlightPersons') !== false) {
        echo "Line " . ($i + 1) . ": " . trim($line) . "\n";
    }
}
