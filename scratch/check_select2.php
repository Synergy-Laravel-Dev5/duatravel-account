<?php
$master = file_get_contents('resources/views/layout/master.blade.php');
echo "Has select2 in master? " . (strpos($master, 'select2') !== false ? 'YES' : 'NO') . "\n";
