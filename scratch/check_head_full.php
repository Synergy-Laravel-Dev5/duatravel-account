<?php
$head_create = shell_exec('git show HEAD:resources/views/expense_transaction/create.blade.php');
$head_edit = shell_exec('git show HEAD:resources/views/expense_transaction/edit.blade.php');

echo "HEAD create first 50 lines:\n";
$lines = explode("\n", $head_create);
echo implode("\n", array_slice($lines, 0, 50)) . "\n";
