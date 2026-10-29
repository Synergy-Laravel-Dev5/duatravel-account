<?php
$f = fopen('C:\\Users\\Arsal\\.gemini\\antigravity\\brain\\441a42a9-1262-4585-b187-d8fd0a87b4ed\\.system_generated\\logs\\transcript_full.jsonl', 'r');
$lineNum = 0;
while (($line = fgets($f)) !== false) {
    if ($lineNum >= 16400 && $lineNum <= 16410) {
        $data = json_decode($line, true);
        echo "Line $lineNum: type=" . ($data['type'] ?? '') . " tool=" . ($data['tool_calls'][0]['name'] ?? ($data['source'] ?? '')) . "\n";
    }
    $lineNum++;
}
fclose($f);
