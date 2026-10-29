<?php
$inv = file_get_contents('resources/views/booking/umrah_invoice.blade.php');
echo "Invoice length: " . strlen($inv) . "\n";
echo "Lines: " . substr_count($inv, "\n") . "\n";

// Let's print the first 120 lines
$lines = explode("\n", $inv);
echo implode("\n", array_slice($lines, 0, 100)) . "\n";
