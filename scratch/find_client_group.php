<?php
$lines = file('routes/web.php');
foreach ($lines as $i => $line) {
    if (strpos($line, 'Route::controller(ClientController') !== false || strpos($line, "prefix('client')") !== false) {
        echo "Line " . ($i + 1) . ": " . $line;
        for ($j = $i + 1; $j < $i + 18; $j++) {
            if (isset($lines[$j])) echo ($j + 1) . ": " . $lines[$j];
        }
    }
}
