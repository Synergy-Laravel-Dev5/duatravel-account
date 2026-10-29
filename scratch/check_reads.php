<?php
$f = fopen('C:\\Users\\Arsal\\.gemini\\antigravity\\brain\\441a42a9-1262-4585-b187-d8fd0a87b4ed\\.system_generated\\logs\\transcript_full.jsonl', 'r');
$lineNum = 0;
while (($line = fgets($f)) !== false) {
    if ($lineNum == 16401 || $lineNum == 16403 || $lineNum == 16405 || $lineNum == 16407) {
        $data = json_decode($line, true);
        echo "Line $lineNum:\n";
        print_r($data['tool_calls']);
    }
    $lineNum++;
}
fclose($f);
