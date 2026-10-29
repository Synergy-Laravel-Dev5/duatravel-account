<?php
$ledger = file_get_contents('resources/views/transaction/ledger.blade.php');
$pos = strpos($ledger, '@foreach($ledgerEntries as $entry)');
if ($pos !== false) {
    echo substr($ledger, $pos, 3500);
}
