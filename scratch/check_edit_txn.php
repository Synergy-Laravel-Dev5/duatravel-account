<?php
$lines = file('resources/views/booking/edit.blade.php');
foreach ($lines as $i => $l) {
    if (stripos($l, 'transaction') !== false) {
        echo ($i + 1) . ": " . trim($l) . "\n";
    }
}
