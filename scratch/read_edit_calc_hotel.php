<?php
$lines = file('resources/views/booking/edit.blade.php');
foreach ($lines as $i => $line) {
    if (strpos($line, 'function calculateHotelCost') !== false) {
        for ($j = $i; $j < $i + 100; $j++) {
            if (isset($lines[$j])) echo ($j + 1) . ": " . $lines[$j];
        }
        break;
    }
}
