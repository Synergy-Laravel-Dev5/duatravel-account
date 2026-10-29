<?php
$lines = file('routes/web.php');
foreach ($lines as $i => $line) {
    if (strpos($line, 'ClientController') !== false) {
        for ($j = $i; $j < $i + 20; $j++) {
            if (isset($lines[$j])) echo ($j + 1) . ": " . $lines[$j];
        }
        break;
    }
}
