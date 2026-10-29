<?php
$create = file_get_contents('resources/views/account/create.blade.php');
echo "Account create len: " . strlen($create) . "\n";
echo "Lines: " . substr_count($create, "\n") . "\n";
echo $create . "\n";
