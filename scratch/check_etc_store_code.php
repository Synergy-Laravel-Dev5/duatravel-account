<?php
$etc = file_get_contents('app/Http/Controllers/ExpenseTransactionController.php');
$pos = strpos($etc, 'public function store');
echo substr($etc, $pos, 3500);
