<?php
$head_edit = file_get_contents('scratch/head_edit.blade.php');
$lines = explode("\n", $head_edit);

foreach ($lines as $i => $line) {
    if (preg_match('/<!--(.*?)-->/', $line, $m)) {
        echo "Line " . ($i+1) . ": " . trim($m[1]) . "\n";
    }
}
