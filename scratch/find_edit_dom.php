<?php
$lines = file('resources/views/booking/edit.blade.php');
foreach ($lines as $i => $line) {
    if (strpos($line, 'DOMContentLoaded') !== false) {
        echo "Line " . ($i + 1) . ": " . trim($line) . "\n";
    }
}
