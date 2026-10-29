<?php
$inv = file_get_contents('resources/views/booking/umrah_invoice.blade.php');
$pos = strpos($inv, 'bottom-section');
if ($pos !== false) {
    echo substr($inv, $pos - 100, 1500) . "\n";
}
