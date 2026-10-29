<?php
$lines = file('app/Http/Controllers/BookingController.php');
foreach ($lines as $i => $line) {
    if (strpos($line, 'function update(') !== false) {
        for ($j = $i; $j < $i + 60; $j++) {
            if (isset($lines[$j])) echo ($j + 1) . ": " . $lines[$j];
        }
        break;
    }
}
