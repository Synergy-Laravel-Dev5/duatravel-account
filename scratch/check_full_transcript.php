<?php
$f = fopen('C:\\Users\\Arsal\\.gemini\\antigravity\\brain\\441a42a9-1262-4585-b187-d8fd0a87b4ed\\.system_generated\\logs\\transcript_full.jsonl', 'r');
$lineNum = 0;
while (($line = fgets($f)) !== false) {
    if ($lineNum == 16409 || $lineNum == 16410 || $lineNum == 16419 || $lineNum == 16420) {
        $data = json_decode($line, true);
        echo "Line $lineNum type: " . ($data['type'] ?? '') . "\n";
    }
    $lineNum++;
}
fclose($f);
