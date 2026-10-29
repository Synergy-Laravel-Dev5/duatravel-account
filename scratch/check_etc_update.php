<?php
$etc = file_get_contents('app/Http/Controllers/ExpenseTransactionController.php');
$pos = strpos($etc, 'public function update');
if ($pos !== false) {
    echo substr($etc, $pos, 2500);
}
