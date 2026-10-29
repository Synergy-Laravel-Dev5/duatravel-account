<?php
$lines = file('resources/views/booking/create.blade.php');
for ($i = 129; $i < 265; $i++) {
    echo ($i + 1) . ": " . ($lines[$i] ?? '');
}
