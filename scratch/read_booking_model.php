<?php
$lines = file('app/Models/Booking.php');
for ($i = 20; $i < 55; $i++) {
    if (isset($lines[$i])) echo ($i + 1) . ": " . $lines[$i];
}
