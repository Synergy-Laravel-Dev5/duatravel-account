<?php
$b_create = file_get_contents('resources/views/booking/create.blade.php');
$b_edit = file_get_contents('resources/views/booking/edit.blade.php');

preg_match_all('/id="tab-[^"]+"/', $b_create, $matches_create);
preg_match_all('/id="tab-[^"]+"/', $b_edit, $matches_edit);

echo "Create tabs:\n";
print_r($matches_create[0]);

echo "Edit tabs:\n";
print_r($matches_edit[0]);
