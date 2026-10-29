<?php
$lines = file('resources/views/booking/create.blade.php');
for ($i = 1570; $i < 1698; $i++) {
    if (isset($lines[$i])) echo ($i + 1) . ": " . $lines[$i];
}
