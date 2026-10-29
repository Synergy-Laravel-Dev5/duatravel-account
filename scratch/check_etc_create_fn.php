<?php
$etc = file_get_contents('app/Http/Controllers/ExpenseTransactionController.php');
$pos = strpos($etc, 'public function create()');
echo substr($etc, $pos, 1000) . "\n";
