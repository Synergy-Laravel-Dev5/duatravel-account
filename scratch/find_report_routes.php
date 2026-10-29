<?php
$web = file_get_contents('routes/web.php');
$lines = explode("\n", $web);
foreach ($lines as $i => $line) {
    if (strpos($line, 'report') !== false || strpos($line, 'Report') !== false) {
        echo ($i + 1) . ': ' . trim($line) . "\n";
    }
}
