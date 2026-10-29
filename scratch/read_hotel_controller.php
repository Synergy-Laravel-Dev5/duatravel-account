<?php
$lines = file('app/Http/Controllers/HotelController.php');
for ($i = count($lines) - 30; $i < count($lines); $i++) {
    if (isset($lines[$i])) echo ($i + 1) . ": " . $lines[$i];
}
