<?php
$lines = file('app/Models/BookingVisa.php');
for ($i = 24; $i < 34; $i++) {
    if (isset($lines[$i])) echo ($i + 1) . ": " . $lines[$i];
}
