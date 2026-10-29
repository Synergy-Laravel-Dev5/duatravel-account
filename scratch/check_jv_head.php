<?php
$head_create = file_get_contents('scratch/head_create.blade.php');
$lines = explode("\n", $head_create);
echo implode("\n", array_slice($lines, 410, 50)) . "\n";
