<?php
$lines = file('resources/views/booking/edit.blade.php');
for ($i = 760; $i < 865; $i++) {
    if (isset($lines[$i])) echo ($i + 1) . ": " . $lines[$i];
}
