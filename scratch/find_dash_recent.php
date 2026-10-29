<?php
$dash = file_get_contents('resources/views/dashboard.blade.php');
$lines = explode("\n", $dash);
foreach ($lines as $i => $line) {
    if (strpos($line, 'recentBookings') !== false || strpos($line, 'Recent Bookings') !== false) {
        echo ($i + 1) . ': ' . trim($line) . "\n";
    }
}
