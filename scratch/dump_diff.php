<?php
// Check the diff of expense_transaction/create.blade.php
$output = shell_exec('git diff resources/views/expense_transaction/create.blade.php');
file_put_contents('scratch/et_create_diff.txt', $output);
echo "Diff length: " . strlen($output) . "\n";
