<?php
$lines = file('resources/views/booking/create.blade.php');
for ($i = 1170; $i < 1215; $i++) {
    if (isset($lines[$i])) echo ($i + 1) . ": " . $lines[$i];
}
