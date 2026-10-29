<?php
$lines = file('resources/views/booking/create.blade.php');
for ($i = 266; $i < 480; $i++) {
    if (isset($lines[$i])) {
        echo ($i + 1) . ": " . $lines[$i];
    }
}
