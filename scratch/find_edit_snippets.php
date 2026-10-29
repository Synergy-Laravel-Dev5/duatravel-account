<?php
$edit = file_get_contents('resources/views/booking/edit.blade.php');

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

findSnippet($edit, 'Booking Voucher No:');
findSnippet($edit, 'name="departure_time"');
findSnippet($edit, 'name="arrival_time"');
findSnippet($edit, 'function buildHotelsOptions()');
findSnippet($edit, 'function addNewHotel(loc');
findSnippet($edit, 'function updateHotelStaySummary()');
