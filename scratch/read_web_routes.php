<?php
$lines = file('routes/web.php');
for ($i = 195; $i < 210; $i++) {
    if (isset($lines[$i])) echo ($i + 1) . ": " . $lines[$i];
}
for ($i = 360; $i < 375; $i++) {
    if (isset($lines[$i])) echo ($i + 1) . ": " . $lines[$i];
}
