<?php
$output = shell_exec('git diff --stat resources/views/expense_transaction/create.blade.php resources/views/expense_transaction/edit.blade.php');
echo $output;
