<?php
$head_edit = file_get_contents('scratch/head_edit.blade.php');

// Find row 2 bank
$pos = strpos($head_edit, '<!-- ROW 2: Bank, Currency, Exchange Rate -->');
echo substr($head_edit, $pos, 1000) . "\n";
