<?php
$lines = file('resources/views/booking/edit.blade.php');
foreach ($lines as $i => $line) {
    if (strpos($line, 'id="tab-visa"') !== false) {
        for ($j = $i; $j < $i + 55; $j++) {
            if (isset($lines[$j])) echo ($j + 1) . ": " . $lines[$j];
        }
        break;
    }
}
