<?php
$output = shell_exec('git diff HEAD resources/views/expense_transaction/create.blade.php');
file_put_contents('scratch/et_create_full_diff.txt', $output);
echo "Full diff written, length: " . strlen($output) . "\n";
