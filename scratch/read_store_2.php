<?php
$lines = file('app/Http/Controllers/BookingController.php');
for ($i = 200; $i < 398; $i++) {
    echo ($i + 1) . ": " . ($lines[$i] ?? '');
}
