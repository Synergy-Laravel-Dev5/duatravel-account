<?php
$b_show = file_get_contents('resources/views/booking/show.blade.php');
preg_match_all('/id="tab-[^"]+"/', $b_show, $matches_show);
print_r($matches_show[0]);
