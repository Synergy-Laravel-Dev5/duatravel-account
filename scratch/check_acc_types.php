<?php
$acc_create = file_get_contents('resources/views/account/create.blade.php');
preg_match('/<select name="account_type".*?<\/select>/s', $acc_create, $m);
if (isset($m[0])) {
    echo $m[0] . "\n";
}
