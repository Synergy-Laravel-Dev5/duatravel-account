<?php
$header = file_get_contents('resources/views/layout/header.blade.php');
preg_match_all('/<a[^>]*href="[^"]*(?:transaction|expense)[^"]*"[^>]*>.*?<\/a>/is', $header, $matches);
print_r($matches[0]);
