<?php
$etc = file_get_contents('app/Http/Controllers/ExpenseTransactionController.php');
$lines = explode("\n", $etc);
echo implode("\n", array_slice($lines, 270, 50)) . "\n";
