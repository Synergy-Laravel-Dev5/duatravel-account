<?php
$lines = file('resources/views/booking/edit.blade.php');
for ($i = 644; $i < 710; $i++) {
    if (isset($lines[$i])) echo ($i + 1) . ": " . $lines[$i];
}
