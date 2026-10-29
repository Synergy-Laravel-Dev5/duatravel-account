<?php
$content = file_get_contents('app/Http/Controllers/DashboardController.php');
$lines = explode("\n", $content);
foreach ($lines as $i => $line) {
    if (strpos($line, "view('dashboard'") !== false || strpos($line, 'compact(') !== false) {
        echo ($i + 1) . ': ' . trim($line) . "\n";
    }
}
