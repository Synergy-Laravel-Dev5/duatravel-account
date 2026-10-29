<?php
$c1 = file_get_contents('scratch/head_create.blade.php');
$c2 = file_get_contents('scratch/head_edit.blade.php');
echo "cashAccounts in head create: " . (strpos($c1, 'cashAccounts') !== false ? 'YES' : 'NO') . "\n";
echo "cashAccounts in head edit: " . (strpos($c2, 'cashAccounts') !== false ? 'YES' : 'NO') . "\n";
