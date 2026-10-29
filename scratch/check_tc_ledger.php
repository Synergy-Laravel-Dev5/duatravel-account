<?php
$tc = file_get_contents('app/Http/Controllers/TransactionController.php');
// Search for ledger method
$pos = strpos($tc, 'public function ledger');
if ($pos !== false) {
    echo substr($tc, $pos, 3500);
}
