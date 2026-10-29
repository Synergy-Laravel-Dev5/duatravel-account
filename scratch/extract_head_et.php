<?php
$headCreate = shell_exec('git show HEAD:resources/views/expense_transaction/create.blade.php');
$headEdit = shell_exec('git show HEAD:resources/views/expense_transaction/edit.blade.php');
echo "HEAD create size: " . strlen($headCreate) . " | HEAD edit size: " . strlen($headEdit) . "\n";
file_put_contents('scratch/head_create.blade.php', $headCreate);
file_put_contents('scratch/head_edit.blade.php', $headEdit);
