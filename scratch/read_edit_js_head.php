<?php
$lines = file('resources/views/booking/edit.blade.php');
for ($i = 1950; $i < 2010; $i++) {
    if (isset($lines[$i])) echo ($i + 1) . ": " . $lines[$i];
}
