<?php
$output = shell_exec('git log -n 3 -p resources/views/expense_transaction/create.blade.php');
file_put_contents('scratch/git_log_create.txt', substr($output, 0, 5000));
echo "Done\n";
