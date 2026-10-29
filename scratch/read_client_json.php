<?php
$lines = file('app/Http/Controllers/ClientController.php');
for ($i = count($lines) - 40; $i < count($lines); $i++) {
    if (isset($lines[$i])) echo ($i + 1) . ": " . $lines[$i];
}
