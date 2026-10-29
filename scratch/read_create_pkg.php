<?php
$lines = file('resources/views/booking/create.blade.php');
for ($i = 89; $i < 130; $i++) {
    if (isset($lines[$i])) echo ($i + 1) . ": " . $lines[$i];
}
