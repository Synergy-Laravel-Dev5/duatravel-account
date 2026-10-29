<?php
$lines = file('resources/views/booking/create.blade.php');
for ($i = 1150; $i < 1300; $i++) {
    if (isset($lines[$i])) {
        echo ($i + 1) . ": " . $lines[$i];
    }
}
