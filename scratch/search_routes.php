<?php
$lines = file('routes/web.php');
foreach ($lines as $i => $line) {
    if (strpos($line, 'json') !== false || strpos($line, 'refresh') !== false) {
        echo "Line " . ($i + 1) . ": " . trim($line) . "\n";
    }
}
