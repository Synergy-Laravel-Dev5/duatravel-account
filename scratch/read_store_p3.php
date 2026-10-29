<?php
$lines = file('app/Http/Controllers/BookingController.php');
for ($i = 270; $i < 370; $i++) {
    if (isset($lines[$i])) echo ($i + 1) . ": " . $lines[$i];
}
