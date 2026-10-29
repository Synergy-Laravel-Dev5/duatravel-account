<?php
$lines = file('resources/views/booking/edit.blade.php');
for ($i = 710; $i < 760; $i++) {
    if (isset($lines[$i])) echo ($i + 1) . ": " . $lines[$i];
}
