<?php
$lines = file('storage/logs/laravel.log');
$updates = [];
foreach ($lines as $line) {
    if (strpos($line, 'Update Request ID 1:  {') !== false) {
        $jsonStr = substr($line, strpos($line, '{'));
        $updates[] = [
            'time' => substr($line, 1, 19),
            'data' => json_decode(trim($jsonStr), true)
        ];
    }
}

echo "Found " . count($updates) . " updates\n";
if (count($updates) >= 2) {
    $u1 = $updates[count($updates)-2]; // previous
    $u2 = $updates[count($updates)-1]; // latest (11:52:54)
    echo "Comparing {$u1['time']} vs {$u2['time']}:\n";

    foreach ($u2['data'] as $k => $v) {
        $old = $u1['data'][$k] ?? null;
        if ($old != $v) {
            echo "Key '{$k}' changed:\n";
            echo "  Old: " . (is_array($old) ? json_encode($old) : $old) . "\n";
            echo "  New: " . (is_array($v) ? json_encode($v) : $v) . "\n";
        }
    }
}
