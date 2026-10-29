<?php
$output = shell_exec('git diff HEAD resources/views/expense_transaction/create.blade.php');
file_put_contents('scratch/create_head_diff.txt', $output);

$outputEdit = shell_exec('git diff HEAD resources/views/expense_transaction/edit.blade.php');
file_put_contents('scratch/edit_head_diff.txt', $outputEdit);

echo "Diff create: " . strlen($output) . " | Diff edit: " . strlen($outputEdit) . "\n";
