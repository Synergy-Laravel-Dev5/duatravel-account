<?php
$inv = file_get_contents('resources/views/booking/umrah_invoice.blade.php');
$pos = strpos($inv, 'TRANSPORT SECTION');
echo substr($inv, $pos, 1500) . "\n";
