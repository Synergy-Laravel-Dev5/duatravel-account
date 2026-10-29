<?php
$bc = file_get_contents('app/Http/Controllers/BookingController.php');
$pos = strpos($bc, 'function umrahInvoice');
if ($pos !== false) {
    echo substr($bc, $pos, 800) . "\n";
} else {
    echo "umrahInvoice not found in BookingController\n";
}
