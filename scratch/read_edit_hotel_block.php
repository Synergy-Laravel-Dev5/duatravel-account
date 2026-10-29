<?php
$lines = file('resources/views/booking/edit.blade.php');
for ($i = 684; $i < 800; $i++) {
    if (isset($lines[$i])) echo ($i + 1) . ": " . $lines[$i];
}
