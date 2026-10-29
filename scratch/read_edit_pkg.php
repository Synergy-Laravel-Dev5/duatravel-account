<?php
$lines = file('resources/views/booking/edit.blade.php');
for ($i = 105; $i < 185; $i++) {
    if (isset($lines[$i])) echo ($i + 1) . ": " . $lines[$i];
}
