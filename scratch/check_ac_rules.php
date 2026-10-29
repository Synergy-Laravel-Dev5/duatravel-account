<?php
$ac = file_get_contents('app/Http/Controllers/AccountController.php');
preg_match('/public function store\(.*?\)\s*\{(.*?)\n    public function/s', $ac, $m);
if (isset($m[1])) {
    echo $m[1] . "\n";
}
