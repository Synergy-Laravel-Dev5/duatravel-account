<?php
$head_create = file_get_contents('scratch/head_create.blade.php');
$pos = strpos($head_create, '<script>');
if ($pos !== false) {
    echo substr($head_create, $pos + 2000, 3000);
}
