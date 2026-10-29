<?php
$lines = file('app/Http/Controllers/PackageController.php');
for ($i = count($lines) - 30; $i < count($lines); $i++) {
    if (isset($lines[$i])) echo ($i + 1) . ": " . $lines[$i];
}
