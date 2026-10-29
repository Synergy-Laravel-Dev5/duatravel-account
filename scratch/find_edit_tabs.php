<?php
$content = file_get_contents(__DIR__ . '/../resources/views/booking/edit.blade.php');
preg_match_all('/id=["\'](tab-[a-z0-9_-]+)["\']/i', $content, $matches, PREG_OFFSET_CAPTURE);
foreach ($matches[1] as $m) {
    $line = substr_count(substr($content, 0, $m[1]), "\n") + 1;
    echo "Tab: {$m[0]} at line {$line}\n";
}
