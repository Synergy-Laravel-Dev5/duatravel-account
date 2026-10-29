<?php
$staff = file_get_contents('resources/views/staff/create.blade.php');
preg_match_all('/.*select2.*/i', $staff, $m);
print_r($m[0]);
