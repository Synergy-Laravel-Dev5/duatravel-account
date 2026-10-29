<?php
$lines = file('resources/views/booking/edit.blade.php');
foreach ($lines as $i => $line) {
    if (strpos($line, '<script>') !== false || strpos($line, '<script') !== false) {
        echo "Line " . ($i + 1) . ": " . trim($line) . "\n";
    }
}
