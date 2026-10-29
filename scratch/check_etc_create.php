<?php
$etc = file_get_contents('app/Http/Controllers/ExpenseTransactionController.php');
preg_match('/ExpenseTransaction::create\(\[(.*?)\]\);/s', $etc, $m);
if (isset($m[1])) {
    echo $m[1] . "\n";
}
