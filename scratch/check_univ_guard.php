<?php
$etc = file_get_contents('app/Http/Controllers/ExpenseTransactionController.php');
$pos = strpos($etc, 'if ($jvMode === \'universal\')');
echo substr($etc, $pos, 600) . "\n";
