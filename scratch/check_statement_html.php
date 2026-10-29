<?php
$ledger = file_get_contents('resources/views/transaction/ledger.blade.php');
// Search for first occurrence of @foreach($statementLines as $row)
$pos = strpos($ledger, '@foreach($statementLines as $row)');
echo substr($ledger, $pos - 200, 1500);
