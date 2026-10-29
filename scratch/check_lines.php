<?php
$head = file('scratch/head_create.blade.php');
$cur = file('resources/views/expense_transaction/create.blade.php');

echo "Head lines: " . count($head) . "\n";
echo "Cur lines: " . count($cur) . "\n";

// Let's see git diff summary
exec('git diff --stat resources/views/expense_transaction/create.blade.php', $diff_stat);
echo implode("\n", $diff_stat) . "\n";
