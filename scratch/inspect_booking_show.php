<?php
$content = file_get_contents('resources/views/booking/show.blade.php');
if (preg_match_all('/voucher[a-zA-Z0-9_]*/i', $content, $matches)) {
    print_r(array_unique($matches[0]));
} else {
    echo "No voucher mentions\n";
}
