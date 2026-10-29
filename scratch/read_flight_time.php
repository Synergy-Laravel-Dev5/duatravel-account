<?php
$lines = file('resources/views/booking/create.blade.php');
for ($i = 370; $i < 410; $i++) {
    if (isset($lines[$i])) {
        echo ($i + 1) . ": " . $lines[$i];
    }
}
