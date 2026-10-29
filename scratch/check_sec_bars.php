<?php
$inv = file_get_contents('resources/views/booking/umrah_invoice.blade.php');
preg_match_all('/<div class="sec-bar">.*?<\/div>/s', $inv, $m);
print_r($m[0]);
