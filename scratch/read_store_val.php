<?php
$lines = file('app/Http/Controllers/BookingController.php');
for ($i = 94; $i < 105; $i++) {
    if (isset($lines[$i])) echo ($i + 1) . ": " . $lines[$i];
}
