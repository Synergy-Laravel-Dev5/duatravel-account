<?php
$output = shell_exec('git diff HEAD app/Http/Controllers/ExpenseTransactionController.php');
file_put_contents('scratch/etc_diff.txt', $output);
echo "ETC diff length: " . strlen($output) . "\n";
