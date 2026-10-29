<?php
$lines = file('resources/views/booking/create.blade.php');
for ($i = 1230; $i < 1260; $i++) {
    if (isset($lines[$i])) echo ($i + 1) . ": " . $lines[$i];
}
