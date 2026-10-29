<?php
$lines = file('app/Http/Controllers/BookingController.php');
for ($i = 520; $i < 590; $i++) {
    if (isset($lines[$i])) echo ($i + 1) . ": " . $lines[$i];
}
