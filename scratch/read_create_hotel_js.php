<?php
$lines = file('resources/views/booking/create.blade.php');
for ($i = 2370; $i < 2515; $i++) {
    if (isset($lines[$i])) echo ($i + 1) . ": " . $lines[$i];
}
