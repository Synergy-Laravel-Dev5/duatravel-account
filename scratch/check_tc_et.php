<?php
$tc = file_get_contents('app/Http/Controllers/TransactionController.php');
$pos = strpos($tc, 'ExpenseTransaction');
if ($pos !== false) {
    echo substr($tc, $pos - 100, 2500);
}
