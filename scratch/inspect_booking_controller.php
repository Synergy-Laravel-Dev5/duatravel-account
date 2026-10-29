<?php

$lines = file('app/Http/Controllers/BookingController.php');
$inStore = false;
$inUpdate = false;
$storeCode = [];
$updateCode = [];

foreach ($lines as $i => $line) {
    if (strpos($line, 'function store(') !== false) {
        $inStore = true;
    }
    if ($inStore) {
        $storeCode[] = ($i + 1) . ": " . $line;
        if (strpos($line, 'function ') !== false && strpos($line, 'function store(') === false) {
            $inStore = false;
        }
    }

    if (strpos($line, 'function update(') !== false) {
        $inUpdate = true;
    }
    if ($inUpdate) {
        $updateCode[] = ($i + 1) . ": " . $line;
        if (strpos($line, 'function ') !== false && strpos($line, 'function update(') === false) {
            $inUpdate = false;
        }
    }
}

echo "=== STORE (lines " . count($storeCode) . ") ===\n";
echo implode("", array_slice($storeCode, 0, 70));
echo "\n...\n";
echo implode("", array_slice($storeCode, -50));

echo "\n=== UPDATE (lines " . count($updateCode) . ") ===\n";
echo implode("", array_slice($updateCode, 0, 70));
echo "\n...\n";
echo implode("", array_slice($updateCode, -50));
