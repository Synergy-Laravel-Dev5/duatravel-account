<?php
$lines = file('app/Http/Controllers/BookingController.php');
for ($i = 610; $i < 725; $i++) {
    if (isset($lines[$i])) echo ($i + 1) . ": " . $lines[$i];
}
