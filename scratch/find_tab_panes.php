<?php

$lines = file('resources/views/booking/create.blade.php');
foreach ($lines as $i => $line) {
    if (stripos($line, 'class="tab-pane') !== false || stripos($line, "class='tab-pane") !== false) {
        echo "Line " . ($i + 1) . ": " . trim($line) . "\n";
    }
}
