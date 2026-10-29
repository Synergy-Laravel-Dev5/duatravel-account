<?php
$lines = file('app/Models/BookingHotel.php');
foreach ($lines as $i => $line) {
    echo ($i + 1) . ": " . $line;
}
