<?php
$lines = file('app/Http/Controllers/BookingController.php');
for ($i = 725; $i < 765; $i++) {
    if (isset($lines[$i])) echo ($i + 1) . ": " . $lines[$i];
}
