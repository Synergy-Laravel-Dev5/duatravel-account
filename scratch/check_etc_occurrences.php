<?php
$etc = file_get_contents('app/Http/Controllers/ExpenseTransactionController.php');
preg_match_all('/ExpenseTransaction::create\(\[(.*?)\]\);/s', $etc, $matches);
foreach ($matches[0] as $i => $m) {
    echo "=== Occurrence $i ===\n";
    echo substr($m, 0, 300) . "\n...\n";
}
