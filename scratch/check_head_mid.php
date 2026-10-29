<?php
$head_create = shell_exec('git show HEAD:resources/views/expense_transaction/create.blade.php');
$lines = explode("\n", $head_create);
echo implode("\n", array_slice($lines, 50, 100)) . "\n";
