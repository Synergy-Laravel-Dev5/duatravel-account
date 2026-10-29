<?php
$lines = file('resources/views/booking/edit.blade.php');
for ($i = 643; $i < 730; $i++) {
    if (isset($lines[$i])) echo ($i + 1) . ": " . $lines[$i];
}
