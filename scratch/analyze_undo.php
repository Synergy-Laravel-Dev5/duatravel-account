<?php
// Look at scratch/move_cat_create.php:
// In that script:
// $content = file_get_contents('resources/views/expense_transaction/create.blade.php');
// It read the file as it was before move_cat_create was executed!
// Let's see if we can undo move_cat_create, update_create_view, and update_create_js!
// In move_cat_create:
// It cut out:
// <!-- UNIFIED TRANSACTION CATEGORY / PAYEE SELECTOR -->
// ... to ...
// <!-- SUBMIT BUTTONS -->
// and pasted it right after @csrf !
