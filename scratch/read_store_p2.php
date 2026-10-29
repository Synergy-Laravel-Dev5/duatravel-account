<?php
$lines = file('app/Http/Controllers/BookingController.php');
for ($i = 190; $i < 275; $i++) {
    if (isset($lines[$i])) echo ($i + 1) . ": " . $lines[$i];
}
