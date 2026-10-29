<?php
$head_edit = file_get_contents('scratch/head_edit.blade.php');
$pos = strpos($head_edit, '<!-- 7. JV VOUCHER SECTION -->');
echo substr($head_edit, $pos, 1500) . "\n";
