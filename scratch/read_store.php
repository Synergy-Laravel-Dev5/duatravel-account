<?php
$lines = file('app/Http/Controllers/BookingController.php');
for ($i = 77; $i < 200; $i++) {
    echo ($i + 1) . ": " . ($lines[$i] ?? '');
}
