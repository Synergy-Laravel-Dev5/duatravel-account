<?php
$content = file_get_contents('scratch/head_create.blade.php');
echo "Total length: " . strlen($content) . "\n";
echo "Total lines: " . substr_count($content, "\n") . "\n";

// Let's print line 40 to 195
$lines = explode("\n", $content);
echo "=== Lines 40-195 ===\n";
echo implode("\n", array_slice($lines, 40, 155)) . "\n";
