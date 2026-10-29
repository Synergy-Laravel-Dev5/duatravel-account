<?php
$lines = file('resources/views/booking/create.blade.php');
for ($i = 1450; $i < 1600; $i++) {
    if (isset($lines[$i])) {
        echo ($i + 1) . ": " . $lines[$i];
    }
}
