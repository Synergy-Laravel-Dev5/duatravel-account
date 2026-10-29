<?php
$content = file_get_contents('resources/views/expense_transaction/create.blade.php');

// Let's locate the card: <!-- UNIFIED TRANSACTION CATEGORY / PAYEE SELECTOR -->
$catStartMarker = '<!-- UNIFIED TRANSACTION CATEGORY / PAYEE SELECTOR -->';
$catPos = strpos($content, $catStartMarker);
if ($catPos === false) {
    die("catStartMarker not found");
}

// Find where this card ends (before <!-- SUBMIT BUTTONS -->)
$submitMarker = '<!-- SUBMIT BUTTONS -->';
$submitPos = strpos($content, $submitMarker);
if ($submitPos === false) {
    die("submitMarker not found");
}

$categoryCard = substr($content, $catPos, $submitPos - $catPos);

// Cut out the categoryCard from its current position
$newContent = substr($content, 0, $catPos) . substr($content, $submitPos);

// Now locate where to insert it at top (after <form ... id="expenseForm">\n@csrf\n)
$formMarker = '<form action="{{ route(\'expense.transaction.store\') }}" method="POST" enctype="multipart/form-data" id="expenseForm">';
$formPos = strpos($newContent, $formMarker);
if ($formPos === false) {
    die("formMarker not found");
}

$csrfPos = strpos($newContent, '@csrf', $formPos);
$insertPos = strpos($newContent, "\n", $csrfPos) + 1;

// Insert categoryCard at the top
$newContent = substr($newContent, 0, $insertPos) . "\n" . $categoryCard . "\n" . substr($newContent, $insertPos);

echo "Category card moved to top successfully.\n";
file_put_contents('resources/views/expense_transaction/create.blade.php', $newContent);
