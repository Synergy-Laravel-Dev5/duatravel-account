<?php
$head_create = file_get_contents('scratch/head_create.blade.php');
$cur_create = file_get_contents('resources/views/expense_transaction/create.blade.php');
echo "Head create len: " . strlen($head_create) . "\n";
echo "Cur create len: " . strlen($cur_create) . "\n";

$head_edit = file_get_contents('scratch/head_edit.blade.php');
$cur_edit = file_get_contents('resources/views/expense_transaction/edit.blade.php');
echo "Head edit len: " . strlen($head_edit) . "\n";
echo "Cur edit len: " . strlen($cur_edit) . "\n";
