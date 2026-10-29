<?php
$ledger = file_get_contents('resources/views/transaction/ledger.blade.php');
$pos = strpos($ledger, '<tbody>');
$pos2 = strpos($ledger, '</tbody>', $pos);
echo substr($ledger, $pos, $pos2 - $pos + 8);
