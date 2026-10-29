<?php
$output = shell_exec('git diff HEAD resources/views/expense_transaction/edit.blade.php');
file_put_contents('scratch/et_edit_full_diff.txt', $output);
echo "Edit diff written, length: " . strlen($output) . "\n";
