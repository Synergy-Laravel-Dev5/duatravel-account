<?php
$lines = file('routes/web.php');
for ($i = 160; $i < 195; $i++) {
    if (isset($lines[$i])) echo ($i + 1) . ": " . $lines[$i];
}
echo "--- PACKAGE ROUTES ---\n";
for ($i = 206; $i < 235; $i++) {
    if (isset($lines[$i])) echo ($i + 1) . ": " . $lines[$i];
}
