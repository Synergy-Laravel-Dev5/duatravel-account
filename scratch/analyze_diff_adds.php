<?php
$lines = file('scratch/et_create_full_diff.txt');
$additions = [];
foreach ($lines as $line) {
    if (strpos($line, '+') === 0 && strpos($line, '+++') !== 0) {
        $additions[] = $line;
    }
}
echo "Total added lines in diff: " . count($additions) . "\n";
echo "First 50 added lines:\n";
echo implode('', array_slice($additions, 0, 50));
