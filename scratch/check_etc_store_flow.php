<?php
$etc = file_get_contents('app/Http/Controllers/ExpenseTransactionController.php');
$lines = explode("\n", $etc);
echo "=== Lines 90-230 ===\n";
echo implode("\n", array_slice($lines, 90, 140)) . "\n";
