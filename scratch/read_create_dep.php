<?php
$lines = file('resources/views/booking/create.blade.php');
for ($i = 385; $i < 415; $i++) {
    if (isset($lines[$i])) echo ($i + 1) . ": " . $lines[$i];
}
