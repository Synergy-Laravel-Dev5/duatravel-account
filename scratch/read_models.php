<?php

function readModel($file) {
    echo "=== $file ===\n";
    $lines = file($file);
    foreach ($lines as $i => $line) {
        if (strpos($line, 'fillable') !== false || strpos($line, 'casts') !== false) {
            for ($j = $i; $j < $i + 25; $j++) {
                if (isset($lines[$j])) echo ($j + 1) . ": " . $lines[$j];
                if (isset($lines[$j]) && strpos($lines[$j], '];') !== false) break;
            }
        }
    }
}

readModel('app/Models/BookingTransport.php');
readModel('app/Models/BookingVisa.php');
readModel('app/Models/BookingPerson.php');
readModel('app/Models/Booking.php');
