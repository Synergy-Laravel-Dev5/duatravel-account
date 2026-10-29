<?php
$etc = file_get_contents('app/Http/Controllers/ExpenseTransactionController.php');
$pos = strpos($etc, 'if ($payeeType === \'jv\')');
if ($pos !== false) {
    echo substr($etc, $pos + 5200, 2000);
}
