<?php
$f = fopen('C:\\Users\\Arsal\\.gemini\\antigravity\\brain\\441a42a9-1262-4585-b187-d8fd0a87b4ed\\.system_generated\\logs\\transcript_full.jsonl', 'r');
$lineNum = 0;
while (($line = fgets($f)) !== false) {
    if (strpos($line, 'resources/views/expense_transaction/create.blade.php') !== false) {
        $data = json_decode($line, true);
        if (isset($data['tool_calls'])) {
            foreach ($data['tool_calls'] as $tc) {
                echo "Step $lineNum: tool {$tc['name']}\n";
            }
        }
    }
    $lineNum++;
}
fclose($f);
