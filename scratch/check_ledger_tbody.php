<?php
$ledger = file_get_contents('resources/views/transaction/ledger.blade.php');
$pos = strpos($ledger, '<tbody>');
if ($pos !== false) {
    echo substr($ledger, $pos, 3500);
}
