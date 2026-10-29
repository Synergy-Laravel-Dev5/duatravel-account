<?php

function findTabs($file) {
    echo "=== Scanning $file ===\n";
    $lines = file($file);
    foreach ($lines as $i => $line) {
        if (preg_match('/id="(flight|hotel|visa|transport|persons|general|costing)"/', $line, $m)) {
            echo "Line " . ($i + 1) . ": id=\"{$m[1]}\"\n";
        }
        if (preg_match('/function (syncFlightPersons|syncVisaPersons|syncPersons|calculateFlightCost|calculateHotelCost|divideHotelMainCost)/', $line, $m)) {
            echo "Line " . ($i + 1) . ": function {$m[1]}\n";
        }
    }
}

findTabs('resources/views/booking/create.blade.php');
findTabs('resources/views/booking/edit.blade.php');
