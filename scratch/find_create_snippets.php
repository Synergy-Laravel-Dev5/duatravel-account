<?php
$create = file_get_contents('resources/views/booking/create.blade.php');

function findSnippet($fileContent, $search, $before = 3, $after = 15) {
    $lines = explode("\n", $fileContent);
    foreach ($lines as $i => $line) {
        if (strpos($line, $search) !== false) {
            $start = max(0, $i - $before);
            $end = min(count($lines) - 1, $i + $after);
            echo "=== Found '$search' at line " . ($i + 1) . " ===\n";
            for ($j = $start; $j <= $end; $j++) {
                echo ($j + 1) . ": " . $lines[$j] . "\n";
            }
            return;
        }
    }
    echo "NOT FOUND: '$search'\n";
}

findSnippet($create, 'function buildHotelsOptions()');
findSnippet($create, 'function addNewHotel(loc');
findSnippet($create, 'function updateHotelStaySummary()');
findSnippet($create, 'flight-time-format-radio');
