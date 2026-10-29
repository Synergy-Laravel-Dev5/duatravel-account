<?php
$lines = file('resources/views/booking/edit.blade.php');
for ($i = 697; $i < 860; $i++) {
    if (isset($lines[$i])) echo ($i + 1) . ": " . $lines[$i];
}
