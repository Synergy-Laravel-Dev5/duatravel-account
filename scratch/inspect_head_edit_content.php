<?php
$content = file_get_contents('scratch/head_edit.blade.php');
$lines = explode("\n", $content);
echo "=== Edit lines 40-220 ===\n";
echo implode("\n", array_slice($lines, 40, 180)) . "\n";
