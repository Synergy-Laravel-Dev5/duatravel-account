<?php
$lines = file('resources/views/booking/create.blade.php');
foreach ($lines as $i => $line) {
    if (strpos($line, 'calculateVisa') !== false || strpos($line, 'calcVisa') !== false || strpos($line, 'visa_charges') !== false) {
        echo "Line " . ($i + 1) . ": " . trim($line) . "\n";
    }
}
