<?php
$lines = file('app/Http/Controllers/BookingController.php');
for ($i = 590; $i < 710; $i++) {
    if (isset($lines[$i])) echo ($i + 1) . ": " . $lines[$i];
}
