<?php
$html = file_get_contents('resources/views/booking/edit.blade.php');
preg_match_all('/name="([^"]+)"/', $html, $matches);
$names = array_unique($matches[1]);

echo "Total named form inputs in edit.blade.php: " . count($names) . "\n";
foreach ($names as $n) {
    echo "  - {$n}\n";
}
