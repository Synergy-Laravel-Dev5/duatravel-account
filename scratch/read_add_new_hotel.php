<?php
$lines = file('resources/views/booking/create.blade.php');
foreach ($lines as $i => $line) {
    if (strpos($line, 'function addNewHotel') !== false) {
        for ($j = $i; $j < $i + 130; $j++) {
            if (isset($lines[$j])) echo ($j + 1) . ": " . $lines[$j];
        }
        break;
    }
}
