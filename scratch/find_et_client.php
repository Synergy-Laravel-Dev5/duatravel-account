<?php
$tc = file_get_contents('app/Http/Controllers/TransactionController.php');
$pos = strpos($tc, 'client_id');
while ($pos !== false) {
    if (strpos(substr($tc, $pos, 300), 'ExpenseTransaction') !== false) {
        echo substr($tc, $pos - 50, 600) . "\n---\n";
    }
    $pos = strpos($tc, 'client_id', $pos + 1);
}
