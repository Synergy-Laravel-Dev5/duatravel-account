<?php
$ledger = file_get_contents('resources/views/transaction/ledger.blade.php');
$pos = strpos($ledger, '@foreach($statementLines as $row)');
if ($pos !== false) {
    echo substr($ledger, $pos, 2500);
}
