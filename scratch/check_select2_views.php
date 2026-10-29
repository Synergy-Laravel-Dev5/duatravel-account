<?php
$files = glob('resources/views/**/*.blade.php');
$found = [];
foreach ($files as $f) {
    $c = file_get_contents($f);
    if (strpos($c, 'select2') !== false || strpos($c, 'choices') !== false) {
        $found[] = $f;
    }
}
print_r($found);
