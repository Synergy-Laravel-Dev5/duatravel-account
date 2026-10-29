<?php
$lines = file('resources/views/booking/create.blade.php');
for ($i = 780; $i < 820; $i++) {
    if (isset($lines[$i])) echo ($i + 1) . ": " . $lines[$i];
}
