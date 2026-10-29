<?php
$lines = file('resources/views/booking/show.blade.php');
foreach ($lines as $i => $line) {
    if (stripos($line, 'voucher') !== false) {
        echo "Line " . ($i + 1) . ": " . trim($line) . "\n";
    }
}
