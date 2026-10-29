<?php
$lines = file('routes/web.php');
for ($i = 97; $i < 111; $i++) {
    if (isset($lines[$i])) echo ($i + 1) . ": " . $lines[$i];
}
echo "----\n";
for ($i = 206; $i < 222; $i++) {
    if (isset($lines[$i])) echo ($i + 1) . ": " . $lines[$i];
}
