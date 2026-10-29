<?php
$inv = file_get_contents('resources/views/booking/umrah_invoice.blade.php');
$lines = explode("\n", $inv);
echo "=== Lines 100-350 ===\n";
echo implode("\n", array_slice($lines, 100, 200)) . "\n";
