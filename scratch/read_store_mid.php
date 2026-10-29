<?php
$lines = file('app/Http/Controllers/BookingController.php');
for ($i = 175; $i < 350; $i++) {
    if (isset($lines[$i])) echo ($i + 1) . ": " . $lines[$i];
}
