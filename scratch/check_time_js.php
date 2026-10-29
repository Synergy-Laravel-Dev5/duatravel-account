<?php
$create = file_get_contents('resources/views/booking/create.blade.php');
$lines = explode("\n", $create);
$found = false;
foreach ($lines as $i => $line) {
    if (strpos($line, 'flight-time-format-radio') !== false || strpos($line, 'time_format') !== false) {
        echo ($i + 1) . ': ' . trim($line) . "\n";
        $found = true;
    }
}
if (!$found) echo "Not found in JS!\n";
