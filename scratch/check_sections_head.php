<?php
$head_create = file_get_contents('scratch/head_create.blade.php');
$lines = explode("\n", $head_create);

// Find all major comments / sections
foreach ($lines as $i => $line) {
    if (preg_match('/<!--(.*?)-->/', $line, $m)) {
        echo "Line " . ($i+1) . ": " . trim($m[1]) . "\n";
    }
}
