<?php
$lines = file('resources/views/booking/edit.blade.php');
for ($i = 3205; $i < 3335; $i++) {
    if (isset($lines[$i])) echo ($i + 1) . ": " . $lines[$i];
}
