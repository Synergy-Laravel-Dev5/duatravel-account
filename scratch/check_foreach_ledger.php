<?php
$ledger = file_get_contents('resources/views/transaction/ledger.blade.php');
preg_match_all('/@foreach\s*\((.*?)\)/', $ledger, $m);
print_r($m[1]);
