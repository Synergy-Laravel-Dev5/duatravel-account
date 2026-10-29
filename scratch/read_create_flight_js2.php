<?php
$lines = file('resources/views/booking/create.blade.php');
for ($i = 1580; $i < 1645; $i++) {
    if (isset($lines[$i])) echo ($i + 1) . ": " . $lines[$i];
}
