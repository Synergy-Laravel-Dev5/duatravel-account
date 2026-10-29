<?php

function inspectVisa($file) {
    echo "=== $file ===\n";
    $lines = file($file);
    $inTab = false;
    $count = 0;
    foreach ($lines as $i => $line) {
        if (strpos($line, 'id="tab-visa"') !== false) {
            $inTab = true;
        }
        if ($inTab) {
            echo ($i + 1) . ": " . $line;
            $count++;
            if ($count > 60 || (strpos($line, 'id="tab-costing"') !== false)) {
                $inTab = false;
            }
        }
    }
}

inspectVisa('resources/views/booking/create.blade.php');
