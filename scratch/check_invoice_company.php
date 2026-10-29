<?php
$inv = file_get_contents('resources/views/booking/umrah_invoice.blade.php');
$lines = explode("\n", $inv);
echo "=== Lines 450-520 ===\n";
echo implode("\n", array_slice($lines, 450, 70)) . "\n";
