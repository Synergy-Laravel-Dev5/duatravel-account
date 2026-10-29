<?php
$inv = file_get_contents('resources/views/booking/umrah_invoice.blade.php');
$lines = explode("\n", $inv);
echo "=== Lines 300-600 ===\n";
echo implode("\n", array_slice($lines, 300, 300)) . "\n";
