<?php
$edit = file_get_contents('resources/views/booking/edit.blade.php');
$lines = explode("\n", $edit);
foreach ($lines as $i => $line) {
    if (strpos($line, 'Booking Voucher No') !== false || strpos($line, 'id="tab-details"') !== false) {
        echo ($i + 1) . ': ' . trim($line) . "\n";
    }
}
