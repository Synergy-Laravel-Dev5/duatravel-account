<?php
$inv = file_get_contents('resources/views/booking/umrah_invoice.blade.php');
$pos = strpos($inv, 'Transport Detail');
if ($pos !== false) {
    echo substr($inv, $pos - 50, 1500) . "\n";
}
