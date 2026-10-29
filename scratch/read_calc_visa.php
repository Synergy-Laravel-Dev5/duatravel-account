<?php
$lines = file('resources/views/booking/create.blade.php');
foreach ($lines as $i => $line) {
    if (strpos($line, 'function calculateVisaCost') !== false) {
        for ($j = $i; $j < $i + 45; $j++) {
            if (isset($lines[$j])) echo ($j + 1) . ": " . $lines[$j];
        }
        break;
    }
}
