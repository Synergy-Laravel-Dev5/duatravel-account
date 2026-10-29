<?php
$lines = file('app/Http/Controllers/BookingController.php');
for ($i = 90; $i < 115; $i++) {
    if (isset($lines[$i])) echo ($i + 1) . ": " . $lines[$i];
}
