<?php
$lines = file('app/Models/BookingPerson.php');
for ($i = 9; $i < 48; $i++) {
    if (isset($lines[$i])) echo ($i + 1) . ": " . $lines[$i];
}
