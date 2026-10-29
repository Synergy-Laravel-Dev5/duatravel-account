<?php
$bcreate = file_get_contents('resources/views/booking/create.blade.php');
preg_match_all('/.*select2.*/i', $bcreate, $m);
print_r(array_slice($m[0], 0, 10));
