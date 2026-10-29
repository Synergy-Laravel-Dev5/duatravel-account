<?php
$lines = file('resources/views/booking/edit.blade.php');
for ($i = 2083; $i < 2160; $i++) {
    if (isset($lines[$i])) echo ($i + 1) . ": " . $lines[$i];
}
