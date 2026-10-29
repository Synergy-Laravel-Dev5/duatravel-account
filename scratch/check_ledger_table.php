<?php
$ledger = file_get_contents('resources/views/transaction/ledger.blade.php');
// Search for the table thead and tbody
$pos = strpos($ledger, '<table');
if ($pos !== false) {
    echo substr($ledger, $pos, 2500);
}
