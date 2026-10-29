<?php
$inv = file_get_contents('resources/views/booking/umrah_invoice.blade.php');
// Search for numbers like 7620, 579120, 600, 700, 1115, 0120, PK170, PK832, etc.
$patterns = ['7620', '579120', 'PK170', 'PK832', 'ANJUM', 'DALLAH', '0920', '1115', '1920', '0120', 'JED APT MAK HTL'];
foreach ($patterns as $p) {
    if (strpos($inv, $p) !== false) {
        echo "Found '$p' in invoice\n";
    }
}
