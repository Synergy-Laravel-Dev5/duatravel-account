<?php
$inv = file_get_contents('resources/views/booking/umrah_invoice.blade.php');
$lines = explode("\n", $inv);
echo "=== Lines 380-450 ===\n";
echo implode("\n", array_slice($lines, 380, 70)) . "\n";
