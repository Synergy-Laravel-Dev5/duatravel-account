<?php
$etc = file_get_contents('app/Http/Controllers/ExpenseTransactionController.php');
preg_match('/public function edit\(.*?\)\s*\{(.*?)\n    public function update/s', $etc, $m);
if (isset($m[1])) {
    echo $m[1] . "\n";
}
