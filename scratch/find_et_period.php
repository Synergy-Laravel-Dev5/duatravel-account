<?php
$tc = file_get_contents('app/Http/Controllers/TransactionController.php');
$pos = strpos($tc, '$etList =');
if ($pos === false) {
    $pos = strpos($tc, 'ExpenseTransaction::');
    // find all occurrences
    while ($pos !== false) {
        echo substr($tc, $pos, 400) . "\n=====\n";
        $pos = strpos($tc, 'ExpenseTransaction::', $pos + 1);
    }
} else {
    echo substr($tc, $pos, 800) . "\n";
}
