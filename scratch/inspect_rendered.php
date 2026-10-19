<?php
$h = file_get_contents(__DIR__ . '/rendered_edit_7.html');
if (preg_match('/<div id="flightPersonsList"[^>]*>(.*?)<\/div>/s', $h, $m)) {
    echo "flightPersonsList content:\n" . $m[0] . "\n";
} else {
    echo "flightPersonsList NOT FOUND\n";
}

if (preg_match('/<div id="hotelsList"[^>]*>(.*?)<\/div>/s', $h, $m)) {
    echo "hotelsList content length: " . strlen($m[0]) . "\n";
}
