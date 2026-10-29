<?php
// Let's check transcript.jsonl
$lines = file('C:\\Users\\Arsal\\.gemini\\antigravity\\brain\\441a42a9-1262-4585-b187-d8fd0a87b4ed\\.system_generated\\logs\\transcript.jsonl');
echo "Transcript lines: " . count($lines) . "\n";
foreach ($lines as $i => $l) {
    if (strpos($l, 'resources/views/expense_transaction/create.blade.php') !== false) {
        $data = json_decode($l, true);
        echo "Step $i type: " . ($data['type'] ?? '') . " status: " . ($data['status'] ?? '') . "\n";
    }
}
