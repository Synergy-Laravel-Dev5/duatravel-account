<?php
$inv = file_get_contents('resources/views/booking/umrah_invoice.blade.php');
$pos = strpos($inv, 'Transportation');
if ($pos !== false) {
    echo substr($inv, $pos + 1200, 1500) . "\n";
}
