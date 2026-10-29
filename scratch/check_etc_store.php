<?php
$etc = file_get_contents('app/Http/Controllers/ExpenseTransactionController.php');
echo "Has account_id in store? " . (strpos($etc, 'account_id') !== false ? 'YES' : 'NO') . "\n";
// Let's see store method lines around bank_id
preg_match('/public function store\(.*?\)\s*\{(.*?)\n    public function/s', $etc, $m);
if (isset($m[1])) {
    echo substr($m[1], 0, 1500) . "\n";
}
