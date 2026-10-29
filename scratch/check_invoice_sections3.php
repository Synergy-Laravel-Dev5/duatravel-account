<?php
$inv = file_get_contents('resources/views/booking/umrah_invoice.blade.php');
$lines = explode("\n", $inv);
echo "=== Lines 600-888 ===\n";
echo implode("\n", array_slice($lines, 600, 288)) . "\n";
