<?php
$routes = file_get_contents('routes/web.php');
preg_match_all('/.*umrah-invoice.*/', $routes, $m);
print_r($m[0]);
