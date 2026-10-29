<?php
$lines = file('resources/views/booking/edit.blade.php');
for ($i = 164; $i < 295; $i++) {
    echo ($i + 1) . ": " . ($lines[$i] ?? '');
}
