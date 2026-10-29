<?php
$html = file_get_contents('scratch/rendered_invoice_1.html');
preg_match('/<!-- BOTTOM PRICE & SUMMARY SECTION -->(.*?)<!-- FOOTER/s', $html, $m);
if (isset($m[1])) {
    echo strip_tags($m[1]) . "\n";
}
