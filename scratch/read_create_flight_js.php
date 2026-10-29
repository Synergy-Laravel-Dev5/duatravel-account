<?php
$lines = file('resources/views/booking/create.blade.php');
echo "--- Flight Departure lines 380-425 ---\n";
for ($i = 380; $i < 425; $i++) {
    if (isset($lines[$i])) echo ($i + 1) . ": " . $lines[$i];
}
echo "--- syncFlightPersons lines 1517-1580 ---\n";
for ($i = 1517; $i < 1580; $i++) {
    if (isset($lines[$i])) echo ($i + 1) . ": " . $lines[$i];
}
