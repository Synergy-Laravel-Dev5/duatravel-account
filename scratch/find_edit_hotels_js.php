<?php
$edit = file_get_contents('resources/views/booking/edit.blade.php');
$lines = explode("\n", $edit);
foreach ($lines as $i => $line) {
    if (strpos($line, 'hotelsData') !== false || strpos($line, 'hotelsOptions') !== false) {
        echo ($i + 1) . ': ' . trim($line) . "\n";
    }
}
