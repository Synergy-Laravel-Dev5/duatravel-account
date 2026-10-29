<?php
$lines = file('resources/views/booking/edit.blade.php');
for ($i = 505; $i < 560; $i++) {
    if (isset($lines[$i])) echo ($i + 1) . ": " . $lines[$i];
}
