<?php
$lines = file('app/Http/Controllers/BookingController.php');
for ($i = 400; $i < 420; $i++) {
    if (isset($lines[$i])) echo ($i + 1) . ": " . $lines[$i];
}
