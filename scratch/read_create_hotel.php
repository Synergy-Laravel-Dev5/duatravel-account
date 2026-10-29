<?php
$lines = file('resources/views/booking/create.blade.php');
for ($i = 481; $i < 555; $i++) {
    if (isset($lines[$i])) echo ($i + 1) . ": " . $lines[$i];
}
