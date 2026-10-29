<?php
$lines = file('app/Models/BookingTransport.php');
for ($i = 25; $i < 38; $i++) {
    if (isset($lines[$i])) echo ($i + 1) . ": " . $lines[$i];
}
